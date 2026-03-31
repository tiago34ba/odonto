<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PagSeguroService;

class PagSeguroController extends Controller
{
    protected $pagSeguroService;

    public function __construct(PagSeguroService $pagSeguroService)
    {
        $this->pagSeguroService = $pagSeguroService;
    }

    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            // Add other necessary validation rules
        ]);

        $paymentResponse = $this->pagSeguroService->createPayment($data);

        return response()->json($paymentResponse);
    }

    public function paymentNotification(Request $request)
    {
        $notificationData = $request->all();

        $this->pagSeguroService->handleNotification($notificationData);

        return response()->json(['status' => 'success']);
    }

    public function getPaymentStatus($paymentId)
    {
        $status = $this->pagSeguroService->getPaymentStatus($paymentId);

        return response()->json($status);
    }
}
