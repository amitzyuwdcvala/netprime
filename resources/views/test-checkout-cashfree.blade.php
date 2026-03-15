<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Cashfree Checkout – NetPrime</title>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 0 16px;
        }

        h1 {
            font-size: 1.25rem;
            color: #333;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 8px 16px;
            background: #e5e7eb;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .tab.active {
            background: #6366f1;
            color: #fff;
        }

        .section {
            display: none;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .section.active {
            display: block;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .field input, .field select {
            width: 100%;
            padding: 10px 12px;
            box-sizing: border-box;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }

        button.primary {
            background: #6366f1;
            color: #fff;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 6px;
            width: 100%;
            margin-top: 8px;
        }

        button.primary:hover {
            background: #4f46e5;
        }

        button.primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .result {
            margin-top: 20px;
            padding: 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
        }

        .result.error {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .result.info {
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .result h3 {
            margin-top: 0;
            font-size: 1rem;
        }

        .result pre {
            background: #fff;
            padding: 12px;
            overflow-x: auto;
            font-size: 11px;
            border-radius: 4px;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .note {
            font-size: 0.8rem;
            color: #666;
            margin-top: 8px;
        }

        .loader {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <h1>Test Cashfree Checkout</h1>
    <p>Test the complete Cashfree payment flow from order creation to verification.</p>

    <div class="tabs">
        <button class="tab active" data-tab="full">Full Flow (Recommended)</button>
        <button class="tab" data-tab="manual">Manual (Use Existing Order)</button>
    </div>

    <!-- Full Flow Section -->
    <div id="full" class="section active">
        <h3 style="margin-top: 0;">Step 1: Create Order & Pay</h3>

        <div class="field">
            <label for="android_id">Android ID (for testing)</label>
            <input type="text" id="android_id" placeholder="e.g. test_device_123" value="test_web_checkout_{{ time() }}">
        </div>

        <div class="field">
            <label for="plan_select">Select Plan</label>
            <select id="plan_select">
                <option value="">Loading plans...</option>
            </select>
        </div>

        <button type="button" class="primary" id="create_order_btn">
            Create Order & Open Cashfree Checkout
        </button>

        <div id="order_result" class="result info" style="display: none;">
            <h3>Order Created</h3>
            <pre id="order_details"></pre>
        </div>
    </div>

    <!-- Manual Section -->
    <div id="manual" class="section">
        <h3 style="margin-top: 0;">Use Existing Payment Session</h3>
        <p class="note">If you already have a payment_session_id from Postman, enter it here:</p>

        <div class="field">
            <label for="session_id">Payment Session ID</label>
            <input type="text" id="session_id" placeholder="session_xxxxx">
        </div>

        <button type="button" class="primary" id="open_checkout_btn">
            Open Cashfree Checkout
        </button>
    </div>

    <!-- Payment Result (shown for both tabs) -->
    <div id="payment_result" class="result" style="display: none;">
        <h3 id="payment_status">Payment Status</h3>
        <pre id="payment_details"></pre>
        <p class="note" id="verify_note"></p>
    </div>

    <script>
        const API_BASE = '{{ url("/api/v1") }}';
        let currentOrderData = null;

        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.onclick = function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            };
        });

        // Load plans on page load
        async function loadPlans() {
            try {
                const res = await fetch(API_BASE + '/subscription/plans', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                const select = document.getElementById('plan_select');
                select.innerHTML = '';

                if (data.status && data.data && data.data.plans) {
                    data.data.plans.forEach(plan => {
                        const opt = document.createElement('option');
                        opt.value = plan.id;
                        opt.textContent = `${plan.name} - ₹${plan.amount} (${plan.days} days)`;
                        select.appendChild(opt);
                    });
                } else {
                    select.innerHTML = '<option value="">No plans available</option>';
                }
            } catch (e) {
                console.error('Failed to load plans:', e);
                document.getElementById('plan_select').innerHTML = '<option value="">Error loading plans</option>';
            }
        }
        loadPlans();

        // Initialize Cashfree SDK
        const cashfree = Cashfree({ mode: "sandbox" }); // Change to "production" for live

        // Full flow: Create order and open checkout
        document.getElementById('create_order_btn').onclick = async function() {
            const btn = this;
            const androidId = document.getElementById('android_id').value.trim();
            const planId = document.getElementById('plan_select').value;

            if (!androidId) {
                alert('Enter an Android ID');
                return;
            }
            if (!planId) {
                alert('Select a plan');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="loader"></span> Creating order...';

            try {
                // First register the user
                const regRes = await fetch(API_BASE + '/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Android-Id': androidId
                    }
                });
                const regData = await regRes.json();
                console.log('Register response:', regData);

                // Create order
                const orderRes = await fetch(API_BASE + '/payment/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Android-Id': androidId
                    },
                    body: JSON.stringify({ plan_id: planId })
                });
                const orderData = await orderRes.json();
                console.log('Create order response:', orderData);

                if (!orderData.status || !orderData.data) {
                    throw new Error(orderData.message || 'Failed to create order');
                }

                currentOrderData = {
                    ...orderData.data,
                    android_id: androidId
                };

                // Show order details
                document.getElementById('order_details').textContent = JSON.stringify(orderData.data, null, 2);
                document.getElementById('order_result').style.display = 'block';

                // Open Cashfree checkout
                if (orderData.data.payment_session_id) {
                    btn.innerHTML = '<span class="loader"></span> Opening checkout...';
                    openCashfreeCheckout(orderData.data.payment_session_id);
                } else {
                    throw new Error('No payment_session_id received');
                }

            } catch (e) {
                console.error('Error:', e);
                alert('Error: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Create Order & Open Cashfree Checkout';
            }
        };

        // Manual: Open checkout with existing session
        document.getElementById('open_checkout_btn').onclick = function() {
            const sessionId = document.getElementById('session_id').value.trim();
            if (!sessionId) {
                alert('Enter a Payment Session ID');
                return;
            }
            openCashfreeCheckout(sessionId);
        };

        // Open Cashfree checkout
        function openCashfreeCheckout(paymentSessionId) {
            const checkoutOptions = {
                paymentSessionId: paymentSessionId,
                redirectTarget: "_modal" // Opens in modal
            };

            cashfree.checkout(checkoutOptions).then(function(result) {
                console.log('Cashfree checkout result:', result);

                if (result.error) {
                    showPaymentResult('error', 'Payment Failed', {
                        error: result.error,
                        order_data: currentOrderData
                    });
                } else if (result.redirect) {
                    showPaymentResult('info', 'Payment Redirected', {
                        message: 'User was redirected. Check webhook logs for status.',
                        order_data: currentOrderData
                    });
                } else if (result.paymentDetails) {
                    // Payment completed - now verify
                    const paymentDetails = result.paymentDetails;
                    showPaymentResult('success', 'Payment Completed!', {
                        payment_details: paymentDetails,
                        order_data: currentOrderData,
                        verify_payload: currentOrderData ? {
                            transaction_id: currentOrderData.transaction_id,
                            gateway_order_id: currentOrderData.gateway_order_id,
                            gateway_payment_id: paymentDetails.cf_payment_id || null,
                            gateway_signature: null
                        } : null
                    });

                    // Auto-verify if we have order data
                    if (currentOrderData) {
                        verifyPayment(currentOrderData, paymentDetails);
                    }
                }
            });
        }

        // Verify payment
        async function verifyPayment(orderData, paymentDetails) {
            try {
                const verifyRes = await fetch(API_BASE + '/payment/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Android-Id': orderData.android_id
                    },
                    body: JSON.stringify({
                        transaction_id: orderData.transaction_id,
                        gateway_order_id: orderData.gateway_order_id,
                        gateway_payment_id: paymentDetails?.cf_payment_id || null,
                        gateway_signature: null
                    })
                });
                const verifyData = await verifyRes.json();
                console.log('Verify response:', verifyData);

                const resultEl = document.getElementById('payment_result');
                const noteEl = document.getElementById('verify_note');

                if (verifyData.status) {
                    noteEl.innerHTML = '<strong style="color: green;">✓ Verification successful!</strong> ' +
                        'The webhook job has been dispatched. Check your subscription status.';
                } else {
                    noteEl.innerHTML = '<strong style="color: orange;">⚠ Verification response:</strong> ' +
                        verifyData.message;
                }
            } catch (e) {
                console.error('Verify error:', e);
                document.getElementById('verify_note').innerHTML =
                    '<strong style="color: red;">✗ Verification failed:</strong> ' + e.message;
            }
        }

        // Show payment result
        function showPaymentResult(type, title, data) {
            const resultEl = document.getElementById('payment_result');
            const statusEl = document.getElementById('payment_status');
            const detailsEl = document.getElementById('payment_details');

            resultEl.className = 'result ' + (type === 'error' ? 'error' : type === 'info' ? 'info' : '');
            statusEl.textContent = title;
            detailsEl.textContent = JSON.stringify(data, null, 2);
            resultEl.style.display = 'block';
            document.getElementById('verify_note').textContent = '';
        }
    </script>
</body>

</html>
