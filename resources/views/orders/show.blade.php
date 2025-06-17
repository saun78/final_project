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
                            <a href="{{ route('orders.edit', $order) }}" class="btn btn-warning btn-sm">
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
                                            @if($item->product->sku)
                                                <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="3" class="text-end">Total:</th>
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