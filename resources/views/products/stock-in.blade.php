@extends('layouts.app')

@section('title', 'Stock In - ' . $product->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Stock In</h1>
            <p class="text-muted">Add inventory for {{ $product->name }} ({{ $product->part_number }})</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    <!-- Product Info Card -->
    <div class="row">
        <div class="col-md-8">
            <!-- Stock In Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-box-arrow-in-down"></i> Add New Stock Batch
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('products.stock-in', $product) }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="{{ old('quantity') }}" 
                                       min="1" 
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Purchase Price -->
                            <div class="col-md-6">
                                <label for="purchase_price" class="form-label">Purchase Price ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('purchase_price') is-invalid @enderror" 
                                           id="purchase_price" 
                                           name="purchase_price" 
                                           value="{{ old('purchase_price', $product->purchase_price) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Cost price for this batch</small>
                            </div>

                            <!-- Selling Price -->
                            <div class="col-md-6">
                                <label for="selling_price" class="form-label">Selling Price ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('selling_price') is-invalid @enderror" 
                                           id="selling_price" 
                                           name="selling_price" 
                                           value="{{ old('selling_price', $product->selling_price) }}" 
                                           step="0.01" 
                                           min="0">
                                    @error('selling_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Update product selling price (affects all future sales)</small>
                            </div>

                            <!-- Received Date -->
                            <div class="col-md-6">
                                <label for="received_date" class="form-label">Received Date</label>
                                <input type="date" 
                                       class="form-control @error('received_date') is-invalid @enderror" 
                                       id="received_date" 
                                       name="received_date" 
                                       value="{{ old('received_date', date('Y-m-d')) }}">
                                @error('received_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Supplier Reference -->
                            <div class="col-md-6">
                                <label for="supplier_ref" class="form-label">Supplier Reference</label>
                                <input type="text" 
                                       class="form-control @error('supplier_ref') is-invalid @enderror" 
                                       id="supplier_ref" 
                                       name="supplier_ref" 
                                       value="{{ old('supplier_ref') }}" 
                                       placeholder="PO number, invoice number, etc.">
                                @error('supplier_ref')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Profit Margin Display -->
                            <div class="col-12">
                                <div class="alert alert-info" id="profitAlert" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Estimated Total Value:</strong>
                                            <span id="totalValue">$0.00</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Profit per Unit:</strong>
                                            <span id="profitPerUnit">$0.00</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Profit Margin:</strong>
                                            <span id="profitMargin">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Any additional notes about this batch...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Add Stock
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Product Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Product Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($product->picture)
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $product->picture) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded" 
                                 style="max-height: 200px;">
                        </div>
                    @endif
                    
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Part Number:</th>
                            <td>{{ $product->part_number }}</td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>{{ $product->category->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th>Brand:</th>
                            <td>{{ $product->brand->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th>Current Stock:</th>
                            <td>
                                <span class="badge bg-{{ $product->quantity > 10 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger') }}">
                                    {{ $product->quantity }} units
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Current Price:</th>
                            <td>${{ number_format($product->purchase_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Selling Price:</th>
                            <td>${{ number_format($product->selling_price, 2) }}</td>
                        </tr>
                        @if($product->location)
                        <tr>
                            <th>Location:</th>
                            <td>{{ $product->location }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Price Update Info -->
                    <div class="alert alert-warning small">
                        <h6><i class="bi bi-info-circle"></i> Price Update Rules</h6>
                        <ul class="mb-0 small">
                            <li><strong>Purchase Price:</strong> Applied only to this batch</li>
                            <li><strong>Selling Price:</strong> Updates product's selling price for all future sales</li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.batches', $product) }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-list-ul"></i> View Batch History
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const quantityInput = document.getElementById('quantity');
    const purchasePriceInput = document.getElementById('purchase_price');
    const sellingPriceInput = document.getElementById('selling_price');
    const profitAlert = document.getElementById('profitAlert');
    const totalValueElement = document.getElementById('totalValue');
    const profitPerUnitElement = document.getElementById('profitPerUnit');
    const profitMarginElement = document.getElementById('profitMargin');
    
    function updateCalculations() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        
        if (quantity > 0 && purchasePrice > 0) {
            // Calculate values
            const totalValue = quantity * purchasePrice;
            const profitPerUnit = sellingPrice - purchasePrice;
            const profitMargin = purchasePrice > 0 ? ((profitPerUnit / purchasePrice) * 100) : 0;
            
            // Update display
            totalValueElement.textContent = '$' + totalValue.toFixed(2);
            profitPerUnitElement.textContent = '$' + profitPerUnit.toFixed(2);
            profitPerUnitElement.className = profitPerUnit >= 0 ? 'text-success' : 'text-danger';
            
            profitMarginElement.textContent = profitMargin.toFixed(1) + '%';
            profitMarginElement.className = profitMargin >= 0 ? 'text-success' : 'text-danger';
            
            // Show the alert
            profitAlert.style.display = 'block';
            
            // Change alert color based on profit
            profitAlert.className = profitPerUnit >= 0 ? 'alert alert-info' : 'alert alert-warning';
        } else {
            // Hide the alert if no valid input
            profitAlert.style.display = 'none';
        }
    }
    
    // Add event listeners
    quantityInput.addEventListener('input', updateCalculations);
    purchasePriceInput.addEventListener('input', updateCalculations);
    sellingPriceInput.addEventListener('input', updateCalculations);
    
    // Initial calculation
    updateCalculations();
});
</script>
@endsection 