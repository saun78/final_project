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
                                <th>Orders</th>
                                <th>Receipt Photo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $activeBatches = $batches->where('quantity', '>', 0);
                                $depletedBatches = $batches->where('quantity', '<=', 0);
                                $firstActiveBatch = $activeBatches->first();
                            @endphp
                            
                            {{-- Active Batches --}}
                            @foreach($activeBatches as $batch)
                            <tr class="{{ $batch->id === $firstActiveBatch?->id ? 'table-warning' : '' }}" title="{{ $batch->id === $firstActiveBatch?->id ? 'Next batch to be used (FIFO)' : '' }}">
                                <td>
                                    <strong>{{ $batch->batch_no }}</strong>
                                    @if($batch->id === $firstActiveBatch?->id)
                                        <span class="badge bg-warning text-dark ms-2">Next Out</span>
                                    @endif
                                    <span class="badge bg-success ms-1">Active</span>
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
                                <td>
                                    @if($batch->orders && $batch->orders->count() > 0)
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#ordersModal{{ $batch->id }}">
                                            <i class="bi bi-receipt"></i> {{ $batch->orders->count() }} Orders
                                        </button>
                                    @else
                                        <span class="text-muted">No sales</span>
                                    @endif
                                </td>
                                <td>
                                    @if($batch->receipt_photo)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#photoModal{{ $batch->id }}">
                                            <i class="bi bi-image"></i> View
                                        </button>
                                    @else
                                        <span class="text-muted">No photo</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            
                            {{-- Depleted Batches --}}
                            @if($depletedBatches->count() > 0)
                                <tr class="table-secondary depleted-header" style="cursor: pointer;" onclick="toggleDepletedBatches()">
                                    <td colspan="8" class="text-center fw-bold text-muted py-2">
                                        <i class="bi bi-archive"></i> Depleted Batches ({{ $depletedBatches->count() }})
                                        <i class="bi bi-chevron-down ms-2" id="depletedChevron"></i>
                                        <small class="ms-2 text-muted">(Click to show/hide)</small>
                                    </td>
                                </tr>
                                @foreach($depletedBatches as $batch)
                                <tr class="table-light text-muted depleted-batch-row" style="display: none;">
                                    <td>
                                        <strong>{{ $batch->batch_no }}</strong>
                                        <span class="badge bg-secondary ms-1">Sold Out</span>
                                        @if($batch->depleted_date)
                                            <br><small class="text-muted">Sold: {{ $batch->depleted_date }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $batch->received_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-dark">0</span>
                                    </td>
                                    <td class="text-muted">${{ number_format($batch->purchase_price, 2) }}</td>
                                    <td class="text-muted">${{ number_format($product->selling_price, 2) }}</td>
                                    <td>
                                        @php
                                            $profit = $product->selling_price - $batch->purchase_price;
                                        @endphp
                                        <span class="text-muted">
                                            ${{ number_format($profit, 2) }}
                                            @if($batch->purchase_price > 0)
                                                <small>({{ number_format(($profit / $batch->purchase_price) * 100, 1) }}%)</small>
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($batch->orders && $batch->orders->count() > 0)
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#ordersModal{{ $batch->id }}">
                                                <i class="bi bi-receipt"></i> {{ $batch->orders->count() }} Orders
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($batch->receipt_photo)
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#photoModal{{ $batch->id }}">
                                                <i class="bi bi-image"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted">No photo</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Current Stock</th>
                                <th>
                                    @php
                                        $activeQuantity = $batches->where('quantity', '>', 0)->sum('quantity');
                                    @endphp
                                    {{ $activeQuantity }} units
                                </th>
                                <th>
                                    @php
                                        $activeBatches = $batches->where('quantity', '>', 0);
                                        $avgPurchasePrice = $activeQuantity > 0 ? $activeBatches->sum(function($batch) { return $batch->quantity * $batch->purchase_price; }) / $activeQuantity : 0;
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
                                <th></th>
                                <th></th>
                            </tr>
                            @if($batches->where('quantity', '<=', 0)->count() > 0)
                            <tr class="text-muted">
                                <th colspan="2">Total Sold</th>
                                <th>
                                    @php
                                        $totalBatches = $batches->count();
                                        $depletedBatches = $batches->where('quantity', '<=', 0)->count();
                                    @endphp
                                    {{ $depletedBatches }} batches
                                </th>
                                <th colspan="5">
                                    <small>Historical data available above</small>
                                </th>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                <!-- FIFO Information -->
                <div class="alert alert-info mt-3">
                    <h6><i class="bi bi-info-circle"></i> FIFO Order & History</h6>
                    @php
                        $nextBatch = $batches->where('quantity', '>', 0)->first();
                        $totalBatches = $batches->count();
                        $activeBatches = $batches->where('quantity', '>', 0)->count();
                        $depletedBatches = $batches->where('quantity', '<=', 0)->count();
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">
                                <strong>Next out:</strong> {{ $nextBatch->batch_no ?? 'No active batches' }} 
                                @if($nextBatch)
                                    - {{ $nextBatch->quantity }} units at ${{ number_format($nextBatch->purchase_price, 2) }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">
                                <strong>History:</strong> {{ $totalBatches }} total batches 
                                ({{ $activeBatches }} active, {{ $depletedBatches }} sold out)
                            </p>
                        </div>
                    </div>
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

    <!-- Photo Modals (outside table structure to prevent DOM conflicts) -->
    @if($batches->count() > 0)
        @foreach($batches as $batch)
            {{-- Photo Modal --}}
            @if($batch->receipt_photo)
                <div class="modal fade" id="photoModal{{ $batch->id }}" tabindex="-1" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Receipt Photo - {{ $batch->batch_no }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center" style="min-height: 400px;">
                                <div class="image-container position-relative">
                                    <div class="loading-spinner position-absolute top-50 start-50 translate-middle" 
                                         id="loading{{ $batch->id }}" 
                                         style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <img src="{{ asset('storage/' . $batch->receipt_photo) }}" 
                                         alt="Receipt for {{ $batch->batch_no }}" 
                                         class="img-fluid rounded shadow receipt-image"
                                         id="receiptImg{{ $batch->id }}"
                                         style="max-height: 70vh; width: auto; opacity: 0; transition: opacity 0.3s ease;"
                                         onload="showImage({{ $batch->id }})"
                                         onerror="hideLoading({{ $batch->id }})">
                                </div>
                                @if($batch->supplier_ref || $batch->notes)
                                    <div class="mt-3 pt-3 border-top">
                                        @if($batch->supplier_ref)
                                            <p class="text-muted mb-1"><strong>Supplier Reference:</strong> {{ $batch->supplier_ref }}</p>
                                        @endif
                                        @if($batch->notes)
                                            <p class="text-muted mb-0"><strong>Notes:</strong> {{ $batch->notes }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <a href="{{ asset('storage/' . $batch->receipt_photo) }}" 
                                   target="_blank" 
                                   class="btn btn-primary">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Orders Modal --}}
            @if($batch->orders && $batch->orders->count() > 0)
                <div class="modal fade" id="ordersModal{{ $batch->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-receipt"></i> Orders Using Batch {{ $batch->batch_no }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Batch Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small><strong>Received:</strong> {{ $batch->received_date->format('M d, Y') }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <small><strong>Purchase Price:</strong> ${{ number_format($batch->purchase_price, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Qty Sold</th>
                                                <th>Unit Cost</th>
                                                <th>Total Cost</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($batch->orders as $orderData)
                                                <tr class="{{ $orderData['is_cancelled'] ? 'table-warning text-muted' : '' }}">
                                                    <td>
                                                        <strong>{{ $orderData['order']->order_number }}</strong>
                                                        @if($orderData['is_cancelled'])
                                                            <br><small class="text-danger">CANCELLED</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $orderData['sale_date']->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $orderData['is_cancelled'] ? 'secondary' : 'primary' }}">
                                                            {{ $orderData['quantity_sold'] }}
                                                        </span>
                                                    </td>
                                                    <td>${{ number_format($orderData['unit_cost'], 2) }}</td>
                                                    <td>
                                                        <strong>${{ number_format($orderData['quantity_sold'] * $orderData['unit_cost'], 2) }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($orderData['is_cancelled'])
                                                            <span class="badge bg-warning text-dark">Cancelled</span>
                                                        @else
                                                            <span class="badge bg-success">Completed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('orders.show', $orderData['order']) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           target="_blank">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th>
                                                    @php
                                                        $totalSold = $batch->orders->where('is_cancelled', false)->sum('quantity_sold');
                                                        $cancelledSold = $batch->orders->where('is_cancelled', true)->sum('quantity_sold');
                                                    @endphp
                                                    {{ $totalSold }} units
                                                    @if($cancelledSold > 0)
                                                        <br><small class="text-muted">(+{{ $cancelledSold }} cancelled)</small>
                                                    @endif
                                                </th>
                                                <th colspan="2">
                                                    <strong>
                                                        ${{ number_format($batch->orders->where('is_cancelled', false)->sum(function($order) { 
                                                            return $order['quantity_sold'] * $order['unit_cost']; 
                                                        }), 2) }}
                                                    </strong>
                                                </th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
@endsection

@push('styles')
<style>
/* Table header styling for consistent height */
.table thead th {
    height: 50px;
    vertical-align: middle;
    padding: 12px 8px;
    line-height: 1.2;
    white-space: nowrap;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-align: center;
    background-color: #f8f9fa;
}

.table thead th:first-child {
    text-align: left;
}

/* Consistent table cell styling */
.table td {
    vertical-align: middle;
    padding: 8px;
}

.table-warning {
    --bs-table-bg: rgba(255, 193, 7, 0.1);
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Modal improvements to prevent flickering */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.fade.show .modal-dialog {
    transform: none;
}

.receipt-image {
    max-width: 100%;
    object-fit: contain;
}

.image-container {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Depleted batches toggle styles */
.depleted-header:hover {
    background-color: rgba(108, 117, 125, 0.2) !important;
}

.depleted-batch-row {
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    'use strict';

    function showImage(batchId) {
        // Hide loading spinner
        const loading = document.getElementById('loading' + batchId);
        if (loading) loading.style.display = 'none';
        
        // Show image with fade in effect
        const img = document.getElementById('receiptImg' + batchId);
        if (img) {
            img.style.opacity = '1';
        }
    }

    function hideLoading(batchId) {
        const loading = document.getElementById('loading' + batchId);
        if (loading) loading.style.display = 'none';
    }

    // Make functions globally available
    window.showImage = showImage;
    window.hideLoading = hideLoading;

    // Initialize photo modal handlers once DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        const photoModals = document.querySelectorAll('[id^="photoModal"]');
        
        photoModals.forEach(function(modal) {
            // Remove any existing listeners to prevent duplicates
            modal.removeEventListener('show.bs.modal', handleModalShow);
            modal.removeEventListener('hidden.bs.modal', handleModalHidden);
            
            // Add fresh listeners for photo modals only
            modal.addEventListener('show.bs.modal', handleModalShow);
            modal.addEventListener('hidden.bs.modal', handleModalHidden);
        });
    });

    function handleModalShow(event) {
        const batchId = this.id.replace('photoModal', '');
        const loading = document.getElementById('loading' + batchId);
        const img = document.getElementById('receiptImg' + batchId);
        
        if (loading && img) {
            // Reset image opacity and show loading
            img.style.opacity = '0';
            loading.style.display = 'block';
            
            // If image is already loaded, show it immediately
            if (img.complete && img.naturalHeight !== 0) {
                showImage(batchId);
            }
        }
    }

    function handleModalHidden(event) {
        const batchId = this.id.replace('photoModal', '');
        const loading = document.getElementById('loading' + batchId);
        const img = document.getElementById('receiptImg' + batchId);
        
        if (loading) loading.style.display = 'none';
        if (img) img.style.opacity = '0';
    }

    // Depleted batches toggle functionality
    window.toggleDepletedBatches = function() {
        const depletedRows = document.querySelectorAll('.depleted-batch-row');
        const chevron = document.getElementById('depletedChevron');
        const isHidden = depletedRows[0] && depletedRows[0].style.display === 'none';
        
        depletedRows.forEach(function(row) {
            if (isHidden) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update chevron icon
        if (chevron) {
            if (isHidden) {
                chevron.className = 'bi bi-chevron-up ms-2';
            } else {
                chevron.className = 'bi bi-chevron-down ms-2';
            }
        }
    };
})();
</script>
@endpush 