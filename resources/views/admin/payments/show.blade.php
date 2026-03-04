@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>{{ $viewData['title'] }}</h1>
        <div class="section-header-button">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back to Payments</a>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12 col-lg-8">
                {{-- Overview card --}}
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Transaction</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Transaction ID</div>
                            <div class="col-md-8 font-weight-bold text-break">{{ $transaction->transaction_id ?? '-' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Android ID</div>
                            <div class="col-md-8">{{ $transaction->android_id ?? '-' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Plan</div>
                            <div class="col-md-8">{{ $transaction->plan ? $transaction->plan->name : '-' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Gateway</div>
                            <div class="col-md-8">{{ $transaction->gateway ? $transaction->gateway->name : '-' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Amount</div>
                            <div class="col-md-8"><strong>{{ $transaction->currency ?? 'INR' }} {{ number_format((float)$transaction->amount, 2) }}</strong></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Status</div>
                            <div class="col-md-8">
                                @php
                                    $statusClass = $transaction->status === \App\Constants\PaymentStatus::SUCCESS ? 'success' : ($transaction->status === \App\Constants\PaymentStatus::FAILED ? 'danger' : 'secondary');
                                @endphp
                                <span class="badge badge-{{ $statusClass }} badge-pill px-3 py-2">{{ $transaction->status }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Paid At</div>
                            <div class="col-md-8">{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y H:i:s') : '-' }}</div>
                        </div>
                        @if($transaction->failed_at)
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Failed At</div>
                            <div class="col-md-8">{{ $transaction->failed_at->format('d M Y H:i:s') }}</div>
                        </div>
                        @endif
                        @if($transaction->error_message)
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Error</div>
                            <div class="col-md-8 text-danger">{{ $transaction->error_message }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Payment method & card/UPI --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Payment method</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Method</div>
                            <div class="col-md-8">{{ $transaction->payment_method ? ucfirst(str_replace('_', ' ', $transaction->payment_method)) : '-' }}</div>
                        </div>
                        @if($transaction->card_last4 || $transaction->card_network)
                        <div class="border rounded p-3 bg-light mb-2">
                            <small class="text-muted d-block mb-2">Card</small>
                            @if($transaction->card_last4)
                                <span class="mr-3">Last 4: <strong>**** {{ $transaction->card_last4 }}</strong></span>
                            @endif
                            @if($transaction->card_network)
                                <span>Network: <strong>{{ ucfirst($transaction->card_network) }}</strong></span>
                            @endif
                        </div>
                        @endif
                        @if($transaction->upi_id)
                        <div class="border rounded p-3 bg-light">
                            <small class="text-muted d-block mb-2">UPI</small>
                            <strong>{{ $transaction->upi_id }}</strong>
                        </div>
                        @endif
                        @if(!$transaction->payment_method && !$transaction->card_last4 && !$transaction->card_network && !$transaction->upi_id)
                        <p class="text-muted mb-0">No payment method details recorded for this transaction.</p>
                        @endif
                    </div>
                </div>

                {{-- Gateway IDs --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Gateway reference</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Order ID</div>
                            <div class="col-md-8 text-break"><code>{{ $transaction->gateway_order_id ?? '-' }}</code></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Payment ID</div>
                            <div class="col-md-8 text-break"><code>{{ $transaction->gateway_payment_id ?? '-' }}</code></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                @if(($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0) || ($transaction->gateway_response && is_array($transaction->gateway_response) && count($transaction->gateway_response) > 0))
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Metadata & response</h4>
                    </div>
                    <div class="card-body">
                        @if($transaction->metadata && is_array($transaction->metadata) && count($transaction->metadata) > 0)
                        <h6 class="text-muted">Metadata</h6>
                        <pre class="bg-dark text-light p-3 rounded small mb-3" style="max-height: 200px; overflow: auto;">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                        @if($transaction->gateway_response && is_array($transaction->gateway_response) && count($transaction->gateway_response) > 0)
                        <h6 class="text-muted">Gateway response</h6>
                        <pre class="bg-dark text-light p-3 rounded small mb-0" style="max-height: 200px; overflow: auto;">{{ json_encode($transaction->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
