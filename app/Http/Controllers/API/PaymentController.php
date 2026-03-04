<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Http\Requests\API\CreateOrderRequest;
use App\Http\Requests\API\VerifyPaymentRequest;
use App\Services\API\PaymentService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponses;

    public $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create payment order
     */
    public function create_order(CreateOrderRequest $request)
    {
        Log::info('[CreateOrder] Request received', [
            'android_id' => $request->user()?->android_id,
            'plan_id' => $request->input('plan_id'),
            'has_user' => (bool) $request->user(),
        ]);

        $response = $this->paymentService->create_order_service($request);

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            Log::warning('[CreateOrder] Returning error response', [
                'status_code' => $statusCode,
                'android_id' => $request->user()?->android_id,
                'plan_id' => $request->input('plan_id'),
            ]);
        }

        return $response;
    }

    /**
     * Verify payment
     */
    public function verify(VerifyPaymentRequest $request)
    {
        return $this->paymentService->verify_payment_service($request);
    }
}

