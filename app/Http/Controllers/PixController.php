<?php

namespace App\Http\Controllers;

use App\Services\PixService;
use Illuminate\Http\Request;

class PixController extends Controller
{
    protected $pixService;

    public function __construct(PixService $pixService)
    {
        $this->pixService = $pixService;
    }

    public function createPayment(Request $request)
    {
        // Validate the request data
        $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        // Create a Pix payment
        $payment = $this->pixService->createPayment($request->amount, $request->description);

        return response()->json($payment);
    }

    public function getPaymentStatus($paymentId)
    {
        // Retrieve the payment status
        $status = $this->pixService->getPaymentStatus($paymentId);

        return response()->json($status);
    }
}
