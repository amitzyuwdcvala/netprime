<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <tr><th>Transaction ID</th><td>{{ $transaction->transaction_id ?? '-' }}</td></tr>
        <tr><th>Android ID</th><td>{{ $transaction->android_id }}</td></tr>
        <tr><th>Plan</th><td>{{ $transaction->plan ? $transaction->plan->name : '-' }}</td></tr>
        <tr><th>Gateway</th><td>{{ $transaction->gateway ? $transaction->gateway->name : '-' }}</td></tr>
        <tr><th>Amount</th><td>{{ $transaction->currency ?? 'INR' }} {{ number_format((float)$transaction->amount, 2) }}</td></tr>
        <tr><th>Status</th><td><span class="badge badge-{{ $transaction->status === 'success' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'secondary') }}">{{ $transaction->status }}</span></td></tr>
        <tr><th>Payment Method</th><td>{{ $transaction->payment_method ?? '-' }}</td></tr>
        <tr><th>Gateway Order ID</th><td>{{ $transaction->gateway_order_id ?? '-' }}</td></tr>
        <tr><th>Gateway Payment ID</th><td>{{ $transaction->gateway_payment_id ?? '-' }}</td></tr>
        <tr><th>Card Last 4</th><td>{{ $transaction->card_last4 ?? '-' }}</td></tr>
        <tr><th>UPI ID</th><td>{{ $transaction->upi_id ?? '-' }}</td></tr>
        <tr><th>Paid At</th><td>{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i:s') : '-' }}</td></tr>
        <tr><th>Failed At</th><td>{{ $transaction->failed_at ? $transaction->failed_at->format('d M Y H:i:s') : '-' }}</td></tr>
        @if($transaction->error_message)
        <tr><th>Error Message</th><td>{{ $transaction->error_message }}</td></tr>
        @endif
        @if($transaction->gateway_response && is_array($transaction->gateway_response))
        <tr><th>Gateway Response</th><td><pre class="mb-0 small">{{ json_encode($transaction->gateway_response, JSON_PRETTY_PRINT) }}</pre></td></tr>
        @endif
        @if($transaction->metadata && is_array($transaction->metadata))
        <tr><th>Metadata</th><td><pre class="mb-0 small">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre></td></tr>
        @endif
    </table>
</div>
