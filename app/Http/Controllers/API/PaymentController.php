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
        Log::info('[VerifyAPI] Request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'android_id' => $request->header('X-Android-ID'),
            'transaction_id' => $request->input('transaction_id'),
            'gateway_order_id' => $request->input('gateway_order_id'),
            'gateway_payment_id' => $request->input('gateway_payment_id'),
            'gateway_signature' => $request->input('gateway_signature') ? substr($request->input('gateway_signature'), 0, 20) . '...' : null,
            'content_type' => $request->header('Content-Type'),
            'all_inputs' => $request->all(),
        ]);

        $response = $this->paymentService->verify_payment_service($request);

        $statusCode = $response->getStatusCode();
        Log::info('[VerifyAPI] Response', [
            'status_code' => $statusCode,
            'transaction_id' => $request->input('transaction_id'),
        ]);

        return $response;
    }


    public function phonepeCallback()
    {
        Log::info('[PhonePeCallback] Browser redirect received', [
            'query' => request()->query(),
            'url' => request()->fullUrl(),
        ]);

        return response()->view('api.phonepe-callback', [], 200)
            ->header('Content-Type', 'text/html');
    }

    public function cashfreeCallback()
    {
        Log::info('[CashfreeCallback] Browser redirect received', [
            'query' => request()->query(),
            'url' => request()->fullUrl(),
        ]);

        return response()->view('api.cashfree-callback', [], 200)
            ->header('Content-Type', 'text/html');
    }
}

