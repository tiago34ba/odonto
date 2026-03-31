<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CardPaymentController extends Controller
{
    private function mercadoPagoToken(): ?string
    {
        return env('MERCADOPAGO_ACCESS_TOKEN');
    }

    public function installments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['required', 'string', 'max:50'],
        ]);

        $token = $this->mercadoPagoToken();
        if (! $token) {
            return response()->json(['message' => 'MERCADOPAGO_ACCESS_TOKEN nao configurado no backend.'], 500);
        }

        $response = Http::acceptJson()->get('https://api.mercadopago.com/v1/payment_methods/installments', [
            'amount' => $validated['amount'],
            'payment_method_id' => $validated['payment_method_id'],
            'access_token' => $token,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Falha ao consultar parcelamento no provedor.',
                'provider_response' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_amount' => ['required', 'numeric', 'min:0.01'],
            'installments' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'string', 'max:50'],
            'payer.email' => ['required', 'email'],
            'payer.identification.type' => ['required', 'string', 'max:10'],
            'payer.identification.number' => ['required', 'string', 'max:20'],
            'card.number' => ['required', 'string', 'max:25'],
            'card.expiration_month' => ['required', 'string', 'max:2'],
            'card.expiration_year' => ['required', 'string', 'max:4'],
            'card.security_code' => ['required', 'string', 'max:4'],
            'card.cardholder.name' => ['required', 'string', 'max:255'],
        ]);

        $token = $this->mercadoPagoToken();
        if (! $token) {
            return response()->json(['message' => 'MERCADOPAGO_ACCESS_TOKEN nao configurado no backend.'], 500);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.mercadopago.com/v1/payments', $validated);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Falha ao processar pagamento com o provedor.',
                'provider_response' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }

    public function status(string $paymentId): JsonResponse
    {
        $token = $this->mercadoPagoToken();
        if (! $token) {
            return response()->json(['message' => 'MERCADOPAGO_ACCESS_TOKEN nao configurado no backend.'], 500);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Falha ao consultar status no provedor.',
                'provider_response' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json());
    }
}
