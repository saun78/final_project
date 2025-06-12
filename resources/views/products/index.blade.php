@extends('layouts.app')

@section('title', 'Parts & Accessories')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Parts & Accessories</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Part
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search parts..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="engine" {{ request('category') == 'engine' ? 'selected' : '' }}>Engine Parts</option>
                        <option value="electrical" {{ request('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                        <option value="body" {{ request('category') == 'body' ? 'selected' : '' }}>Body Parts</option>
                        <option value="accessories" {{ request('category') == 'accessories' ? 'selected' : '' }}>Accessories</option>
                        <option value="maintenance" {{ request('category') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="brand" class="form-select">
                        <option value="">All Brands</option>
                        <option value="honda" {{ request('brand') == 'honda' ? 'selected' : '' }}>Honda</option>
                        <option value="yamaha" {{ request('brand') == 'yamaha' ? 'selected' : '' }}>Yamaha</option>
                        <option value="suzuki" {{ request('brand') == 'suzuki' ? 'selected' : '' }}>Suzuki</option>
                        <option value="kawasaki" {{ request('brand') == 'kawasaki' ? 'selected' : '' }}>Kawasaki</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->part_number }}</td>
                            <td>
                                @if($product->picture)
                                    <img src="{{ asset('storage/' . $product->picture) }}" alt="{{ $product->name }}" 
                                        class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                @else
                                    <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <span class="badge bg-info">{{ $product->category_id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->brand_id }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->quantity > 10 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger') }}">
                                    {{ $product->quantity }}
                                </span>
                            </td>
                            <td>${{ number_format($product->purchase_price, 2) }}</td>
                            <td>${{ number_format($product->selling_price, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary" 
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                        title="Quick View" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            title="Delete" onclick="return confirm('Are you sure you want to delete this part?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Quick View Modal -->
                                <div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $product->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if($product->picture)
                                                            <img src="{{ asset('storage/' . $product->picture) }}" 
                                                                alt="{{ $product->name }}" class="img-fluid rounded">
                                                        @else
                                                            <div class="bg-light text-center p-5 rounded">
                                                                <i class="bi bi-image fs-1"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Part Number:</strong> {{ $product->part_number }}</p>
                                                        <p><strong>Category:</strong> {{ $product->category_id }}</p>
                                                        <p><strong>Brand:</strong> {{ $product->brand_id }}</p>
                                                        <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
                                                        <p><strong>Purchase Price:</strong> ${{ number_format($product->purchase_price, 2) }}</p>
                                                        <p><strong>Selling Price:</strong> ${{ number_format($product->selling_price, 2) }}</p>
                                                        <p><strong>Description:</strong></p>
                                                        <p>{{ $product->description }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No parts found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 