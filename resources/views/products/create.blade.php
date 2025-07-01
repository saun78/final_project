@extends('layouts.app')

@section('title', 'Add New Part')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Add New Part</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="part_number" class="form-label">Part Number</label>
                                <input type="text" class="form-control @error('part_number') is-invalid @enderror" 
                                    id="part_number" name="part_number" value="{{ old('part_number') }}">
                                @error('part_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Part Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="category_id" class="form-label mb-0">Category</label>
                                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <label for="brand_id" class="form-label mb-0">Brand</label>
                                    <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('brand_id') is-invalid @enderror" 
                                    id="brand_id" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
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
                                    <label for="supplier_id" class="form-label mb-0">Supplier</label>
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="bi bi-plus-lg"></i> Add New
                                    </a>
                                </div>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" 
                                    id="supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
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
                                id="location" name="location" value="{{ old('location') }}" 
                                placeholder="e.g. Warehouse A, Store Floor, etc.">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Initial Stock and Pricing -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Initial Stock & Batch Creation</h6>
                            <p class="mb-0 small">
                                If you set an initial quantity > 0, the system will automatically create the first batch record 
                                with the specified purchase price. You can add more stock batches later using the "Stock In" feature.
                            </p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Initial Quantity</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                    id="quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0" required>
                                <small class="text-muted">Will create initial batch if > 0</small>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="purchase_price" class="form-label">Purchase Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('purchase_price') is-invalid @enderror" 
                                        id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" 
                                        step="0.01" min="0" required>
                                </div>
                                <small class="text-muted">Cost price for initial batch</small>
                                @error('purchase_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="selling_price" class="form-label">Selling Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror" 
                                        id="selling_price" name="selling_price" value="{{ old('selling_price') }}" 
                                        step="0.01" min="0" required>
                                </div>
                                <small class="text-muted">Sales price for all transactions</small>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="picture" class="form-label">Part Image</label>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Part
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 