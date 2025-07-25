@extends('layouts.app')

@section('title', 'Edit Order Payment Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Payment Details - {{ $order->order_number }}</h4>
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Order
                    </a>
                </div>
                <div class="card-body">
                    <!-- Notice -->
                    <div class="alert alert-warning" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong>Limited Editing:</strong> Only payment method and labor fee can be changed. Product items cannot be modified to maintain batch inventory integrity.
                            </div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('orders.update', $order) }}" method="POST" id="paymentForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Current Order Items (Read-only) -->
                        <div class="mb-4">
                            <h5>Order Items (Cannot be changed)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Product/Part</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->orderItems as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->product->name ?? 'N/A' }}
                                                    @if($item->product && $item->product->part_number)
                                                        <br><small class="text-muted">Part #: {{ $item->product->part_number }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>${{ number_format($item->price, 2) }}</td>
                                                <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Items Subtotal:</th>
                                            <th id="itemsSubtotal">${{ number_format($order->orderItems->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Editable Payment Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5>Payment Details (Editable)</h5>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Payment Method</label>
                                            <select name="payment_method" class="form-select" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="cash" {{ $order->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="bank_transfer" {{ $order->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                <option value="tng_wallet" {{ $order->payment_method == 'tng_wallet' ? 'selected' : '' }}>TNG Wallet</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Labor Fee</label>
                                            <input type="number" name="labor_fee" id="laborFee" class="form-control" 
                                                   step="0.01" min="0" value="{{ $order->labor_fee ?? 0 }}" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Updated Order Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Items Subtotal:</span>
                                            <span id="displaySubtotal">${{ number_format($order->orderItems->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Labor Fee:</span>
                                            <span id="displayLaborFee">${{ number_format($order->labor_fee ?? 0, 2) }}</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Amount:</strong>
                                            <strong id="displayTotal">${{ number_format($order->amount, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Payment Details
                                </button>
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const laborFeeInput = document.getElementById('laborFee');
    const itemsSubtotal = {{ $order->orderItems->sum(function($item) { return $item->quantity * $item->price; }) }};
    
    function updateTotal() {
        const laborFee = parseFloat(laborFeeInput.value) || 0;
        const total = itemsSubtotal + laborFee;
        
        document.getElementById('displayLaborFee').textContent = '$' + laborFee.toFixed(2);
        document.getElementById('displayTotal').textContent = '$' + total.toFixed(2);
    }
    
    laborFeeInput.addEventListener('input', updateTotal);
});
</script>
@endpush 