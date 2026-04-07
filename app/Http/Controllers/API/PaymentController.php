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

        $response = $this->paymentService->verify_payment_service($request);

        $statusCode = $response->getStatusCode();

        return $response;
    }


    public function phonepeCallback()
    {

        return response()->view('api.phonepe-callback', [], 200)
            ->header('Content-Type', 'text/html');
    }

    public function cashfreeCallback()
    {

        return response()->view('api.cashfree-callback', [], 200)
            ->header('Content-Type', 'text/html');
    }
}

