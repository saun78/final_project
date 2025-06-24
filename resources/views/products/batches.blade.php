@extends('layouts.app')

@section('title', 'Batch Inventory - ' . $product->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Batch Inventory</h1>
            <p class="text-muted">{{ $product->name }} ({{ $product->part_number }})</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('products.stock-in.form', $product) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Stock
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Price Summary -->
   
    <!-- Update Selling Price Card -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-currency-dollar"></i> Selling Price Management
                </h6>
                <span class="text-muted">Current: ${{ number_format($product->selling_price, 2) }}</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update-selling-price', $product) }}" method="POST" class="row g-3 align-items-end">
                @csrf
                @method('PUT')
                
                <div class="col-md-8">
                    <label for="selling_price" class="form-label">New Selling Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control" 
                               id="selling_price" 
                               name="selling_price" 
                               value="{{ $product->selling_price }}" 
                               step="0.01" 
                               min="0" 
                               required>
                    </div>
                    <div class="form-text">This will update the selling price for all future transactions. Batch costs remain unchanged.</div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-clockwise"></i> Update Price
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch Inventory Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list-ul"></i> Batch Details
                <small class="text-muted">(FIFO Order - Oldest First)</small>
            </h6>
        </div>
        <div class="card-body">
            @if($batches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Batch No.</th>
                                <th>Date</th>
                                <th>Quantity</th>
                                <th>Purchase Price</th>
                                <th>Selling Price</th>
                                <th>Profit per Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr class="{{ $loop->first ? 'table-warning' : '' }}" title="{{ $loop->first ? 'Next batch to be used (FIFO)' : '' }}">
                                <td>
                                    <strong>{{ $batch->batch_no }}</strong>
                                    @if($loop->first)
                                        <span class="badge bg-warning text-dark ms-2">Next Out</span>
                                    @endif
                                </td>
                                <td>{{ $batch->received_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $batch->quantity > 50 ? 'success' : ($batch->quantity > 10 ? 'warning' : 'danger') }}">
                                        {{ $batch->quantity }}
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">${{ number_format($batch->purchase_price, 2) }}</td>
                                <td class="fw-bold text-success">${{ number_format($product->selling_price, 2) }}</td>
                                <td>
                                    @php
                                        $profit = $product->selling_price - $batch->purchase_price;
                                    @endphp
                                    <span class="fw-bold text-{{ $profit >= 0 ? 'success' : 'danger' }}">
                                        ${{ number_format($profit, 2) }}
                                        @if($batch->purchase_price > 0)
                                            <small class="text-muted">({{ number_format(($profit / $batch->purchase_price) * 100, 1) }}%)</small>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th>{{ $batches->sum('quantity') }} units</th>
                                <th>
                                    @php
                                        $totalQuantity = $batches->sum('quantity');
                                        $avgPurchasePrice = $totalQuantity > 0 ? $batches->sum(function($batch) { return $batch->quantity * $batch->purchase_price; }) / $totalQuantity : 0;
                                    @endphp
                                    <span class="text-primary">Avg: ${{ number_format($avgPurchasePrice, 2) }}</span>
                                </th>
                                <th class="text-success">${{ number_format($product->selling_price, 2) }}</th>
                                <th>
                                    @php
                                        $avgProfit = $product->selling_price - $avgPurchasePrice;
                                    @endphp
                                    <span class="text-{{ $avgProfit >= 0 ? 'success' : 'danger' }}">
                                        ${{ number_format($avgProfit, 2) }}
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- FIFO Information -->
                <div class="alert alert-info mt-3">
                    <h6><i class="bi bi-info-circle"></i> FIFO Order</h6>
                    <p class="mb-0">
                        <strong>Next out:</strong> {{ $batches->first()->batch_no ?? 'No batches' }} 
                        @if($batches->first())
                            - {{ $batches->first()->quantity }} units at ${{ number_format($batches->first()->purchase_price, 2) }}
                        @endif
                    </p>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No Batch Inventory Found</h4>
                    <p class="text-muted">This product doesn't have any batch inventory yet.</p>
                    <a href="{{ route('products.stock-in.form', $product) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add First Stock Batch
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-warning {
    --bs-table-bg: rgba(255, 193, 7, 0.1);
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush 