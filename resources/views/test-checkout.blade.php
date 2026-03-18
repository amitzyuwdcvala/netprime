<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Razorpay Checkout – FlixyGO</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 560px;
            margin: 40px auto;
            padding: 0 16px;
        }

        h1 {
            font-size: 1.25rem;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .field input {
            width: 100%;
            padding: 8px 12px;
            box-sizing: border-box;
        }

        button {
            background: #3395ff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 6px;
        }

        button:hover {
            background: #2878dd;
        }

        .result {
            margin-top: 24px;
            padding: 16px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
        }

        .result h3 {
            margin-top: 0;
        }

        .result pre {
            background: #fff;
            padding: 12px;
            overflow-x: auto;
            font-size: 12px;
        }

        .note {
            font-size: 0.875rem;
            color: #666;
            margin-top: 8px;
        }

        .error {
            background: #fef2f2;
            border-color: #fecaca;
        }
    </style>
</head>

<body>
    <h1>Test Razorpay Checkout</h1>
    <p>Use this page to complete a test payment after creating an order via API (Postman).</p>

    @if(!$razorpay_key)
        <div class="result error">
            <strong>Razorpay not configured.</strong> Ensure an active payment gateway with code "Razorpay" exists and has
            <code>key_id</code> in credentials.
        </div>
    @else
        <div class="field">
            <label for="order_id">Razorpay Order ID (gateway_order_id from create-order response)</label>
            <input type="text" id="order_id" placeholder="e.g. order_SIVU1bCcKf0tCe">
        </div>
        <div class="field">
            <label for="amount">Amount (INR) – must match order</label>
            <input type="number" id="amount" placeholder="99">
        </div>
        <div class="field">
            <button type="button" id="pay_btn">Pay with Razorpay (Card / UPI)</button>
        </div>

        <div id="payment_result" class="result" style="display: none;">
            <h3>Payment successful</h3>
            <p>Use these values to call <strong>Verify Payment</strong> in Postman:</p>
            <pre id="verify_payload"></pre>
            <p class="note">POST /api/v1/payment/verify with header <code>X-Android-Id: your_android_id</code> and body
                (JSON) with transaction_id, gateway_order_id, gateway_payment_id, gateway_signature.</p>
        </div>
    @endif

    @if($razorpay_key)
        <script>
            document.getElementById('pay_btn').onclick = function () {
                var orderId = document.getElementById('order_id').value.trim();
                var amountRupees = document.getElementById('amount').value;
                if (!orderId) {
                    alert('Enter Razorpay Order ID (gateway_order_id from create-order response).');
                    return;
                }
                var amountPaise = Math.round(parseFloat(amountRupees) * 100);
                if (isNaN(amountPaise) || amountPaise < 100) {
                    alert('Enter a valid amount in INR (e.g. 99).');
                    return;
                }

                var options = {
                    key: "{{ $razorpay_key }}",
                    amount: amountPaise,
                    currency: "INR",
                    order_id: orderId,
                    handler: function (response) {
                        document.getElementById('verify_payload').textContent = JSON.stringify({
                            transaction_id: "(paste from create-order response)",
                            gateway_order_id: response.razorpay_order_id,
                            gateway_payment_id: response.razorpay_payment_id,
                            gateway_signature: response.razorpay_signature
                        }, null, 2);
                        document.getElementById('payment_result').style.display = 'block';
                    }
                };
                var rzp = new Razorpay(options);
                rzp.open();
            };
        </script>
    @endif
</body>

</html>