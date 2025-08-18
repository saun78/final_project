@extends('layouts.app')

@section('title', 'Edit Part')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Part</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="part_number" class="form-label">Part Number</label>
                                <input type="text" class="form-control @error('part_number') is-invalid @enderror" 
                                    id="part_number" name="part_number" value="{{ old('part_number', $product->part_number) }}">
                                @error('part_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Part Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="category_id" class="form-label mb-0">Category <span class="text-danger">*</span></label>
                                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="brand_id" class="form-label mb-0">Brand <span class="text-danger">*</span></label>
                                    <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('brand_id') is-invalid @enderror" 
                                    id="brand_id" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="supplier_id" class="form-label mb-0">Supplier <span class="text-danger">*</span></label>
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                    id="supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->contact_person }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                id="location" name="location" value="{{ old('location', $product->location) }}" 
                                placeholder="e.g. Warehouse A, Store Floor, etc.">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Pricing Information (Read-Only) -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Price Management</h6>
                            <p class="mb-2 small">
                                <strong>Purchase Price:</strong> Set during "Stock In" for each batch. Cannot be edited here.
                            </p>
                            <p class="mb-0 small">
                                <strong>Selling Price:</strong> Update via <a href="{{ route('products.batches', $product) }}" class="alert-link">Batches page</a> 
                                to change for all future transactions.
                            </p>
                        </div>

                        <!-- <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Current Total Quantity</label>
                                <input type="number" class="form-control bg-light" 
                                    id="quantity" value="{{ $product->quantity }}" readonly>
                                <small class="text-muted">Managed through batch system</small>
                            </div>
                            <div class="col-md-4">
                                <label for="purchase_price" class="form-label">Average Purchase Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control bg-light" 
                                        id="purchase_price" 
                                        value="{{ $product->inventoryBatches()->where('quantity', '>', 0)->exists() 
                                                    ? number_format($product->inventoryBatches()->where('quantity', '>', 0)->get()->sum(function($batch) { return $batch->quantity * $batch->purchase_price; }) / $product->inventoryBatches()->where('quantity', '>', 0)->sum('quantity'), 2)
                                                    : number_format($product->purchase_price, 2) }}" 
                                        readonly>
                                </div>
                                <small class="text-muted">Calculated from active batches</small>
                            </div>
                            <div class="col-md-4">
                                <label for="selling_price" class="form-label">Current Selling Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control bg-light" 
                                        id="selling_price" value="{{ number_format($product->selling_price, 2) }}" 
                                        readonly>
                                </div>
                                <small class="text-muted">Update via Batches page</small>
                            </div>
                        </div> -->

                        <div class="mb-3">
                            <label for="picture" class="form-label">Part Image</label>
                            @if($product->picture)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $product->picture) }}" alt="{{ $product->name }}" 
                                        class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('picture') is-invalid @enderror" 
                                id="picture" name="picture" accept="image/*">
                            <div class="form-text">Upload a clear image of the part. Max file size: 2MB</div>
                            @error('picture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="return validateForm()">
                                <i class="bi bi-save"></i> Update Part
                            </button>
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
function validateForm() {
    // Remove any price fields from the form before submission
    const form = document.querySelector('form');
    const priceFields = form.querySelectorAll('#quantity, #purchase_price, #selling_price');
    
    priceFields.forEach(field => {
        if (field.name) {
            field.removeAttribute('name');
        }
    });
    
    return true;
}

// Also remove name attributes on page load to be extra safe
document.addEventListener('DOMContentLoaded', function() {
    const priceFields = document.querySelectorAll('#quantity, #purchase_price, #selling_price');
    priceFields.forEach(field => {
        if (field.name) {
            field.removeAttribute('name');
        }
    });
});
</script>
@endpush 
<style>
    /* Optional: Add subtle background to required fields */
.form-control:required,
.form-select:required {
    border-left: 3px solid #dc3545;
}

/* Optional: Add focus effect for required fields */
.form-control:required:focus,
.form-select:required:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>