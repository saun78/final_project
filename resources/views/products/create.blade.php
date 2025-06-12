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
                                    id="part_number" name="part_number" value="{{ old('part_number') }}" required>
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
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <option value="engine" {{ old('category_id') == 'engine' ? 'selected' : '' }}>Engine Parts</option>
                                    <option value="electrical" {{ old('category_id') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="body" {{ old('category_id') == 'body' ? 'selected' : '' }}>Body Parts</option>
                                    <option value="accessories" {{ old('category_id') == 'accessories' ? 'selected' : '' }}>Accessories</option>
                                    <option value="maintenance" {{ old('category_id') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select class="form-select @error('brand_id') is-invalid @enderror" 
                                    id="brand_id" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    <option value="honda" {{ old('brand_id') == 'honda' ? 'selected' : '' }}>Honda</option>
                                    <option value="yamaha" {{ old('brand_id') == 'yamaha' ? 'selected' : '' }}>Yamaha</option>
                                    <option value="suzuki" {{ old('brand_id') == 'suzuki' ? 'selected' : '' }}>Suzuki</option>
                                    <option value="kawasaki" {{ old('brand_id') == 'kawasaki' ? 'selected' : '' }}>Kawasaki</option>
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                    id="quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0" required>
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