<?php

namespace App\Http\Controllers;

use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Handle incoming notification callback from Midtrans.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
        ]);

        $result = $this->midtransService->handleNotification($payload);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['code'] ?? 200);
    }
}

