@extends('layouts.app')

@section('title', 'Receipt Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Receipt Details - {{ $order->order_number }}</h4>
                    <div>
                        <div class="btn-group" role="group">
                            <a href="{{ route('orders.print', $order) }}" class="btn btn-success btn-sm" target="_blank">
                                <i class="bi bi-printer"></i> Print Preview
                            </a>
                            <a href="{{ route('orders.print.pdf', $order) }}" class="btn btn-danger btn-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Download PDF
                            </a>
                        </div>
                        <div class="btn-group ms-2" role="group">
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-warning btn-sm"
                               title="Edit payment method and labor fee">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>
                                        @if($order->payment_method)
                                            @switch($order->payment_method)
                                                @case('cash')
                                                    <span class="badge bg-success">Cash</span>
                                                    @break
                                                @case('card')
                                                    <span class="badge bg-primary">Card</span>
                                                    @break
                                                @case('tng_wallet')
                                                    <span class="badge bg-warning">TNG Wallet</span>
                                                    @break
                                                @default
                                                    <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                                            @endswitch
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created Date:</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $order->updated_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>Order Summary</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Items:</span>
                                        <span>{{ $order->orderItems->sum('quantity') }}</span>
                                    </div>
                                    @php
                                        $itemsSubtotal = $order->orderItems->sum(function($item) {
                                            return $item->quantity * $item->price;
                                        });
                                    @endphp
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Items Subtotal:</span>
                                        <span>${{ number_format($itemsSubtotal, 2) }}</span>
                                    </div>
                                    @if($order->labor_fee > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Labor Fee:</span>
                                            <span>${{ number_format($order->labor_fee, 2) }}</span>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Amount:</strong>
                                        <strong>${{ number_format($order->amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product/Part</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Selling Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name }}
                                            @if($item->product->part_number)
                                                <br><small class="text-muted">Part #: {{ $item->product->part_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $item->product->supplier->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $itemsTotal = $order->orderItems->sum(function($item) {
                                        return $item->quantity * $item->price;
                                    });
                                @endphp
                                <tr>
                                    <th colspan="4" class="text-end">Items Subtotal:</th>
                                    <th>${{ number_format($itemsTotal, 2) }}</th>
                                </tr>
                                @if($order->labor_fee > 0)
                                    <tr>
                                        <th colspan="4" class="text-end">Labor Fee:</th>
                                        <th>${{ number_format($order->labor_fee, 2) }}</th>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th>${{ number_format($order->amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 