@extends('layouts.app')

@section('title', 'Parts & Accessories')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Parts & Accessories</h1>
        <div class="btn-group">
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add New Part
        </a>
            <a href="{{ route('manage.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear"></i> Manage Categories & Brands
            </a>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3" id="searchForm">
                <div class="col-md-4">
                    <div class="position-relative">
                        <input type="text" name="search" class="form-control" placeholder="Search parts... (Press / to focus)" value="{{ request('search') }}" id="searchInput">
                        <div class="position-absolute top-50 end-0 translate-middle-y pe-3" id="searchLoader" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Searching...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select" id="categorySelect">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="brand" class="form-select" id="brandSelect">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-grid">
                        @if(request('search') || request('category') || request('brand'))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Clear
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Location</th>
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
                                <span class="badge bg-info">{{ $product->category ? $product->category->name : 'Unknown' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $product->brand ? $product->brand->name : 'Unknown' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $product->location ?: 'Unknown' }}</span>
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
                                        title="Quick View" 
                                        onclick="showProductModal({
                                            id: {{ $product->id }},
                                            name: '{{ addslashes($product->name) }}',
                                                                        part_number: '{{ $product->part_number }}',
                            category: '{{ $product->category ? addslashes($product->category->name) : 'Unknown' }}',
                            brand: '{{ $product->brand ? addslashes($product->brand->name) : 'Unknown' }}',
                            location: '{{ $product->location ? addslashes($product->location) : 'Unknown' }}',
                            quantity: {{ $product->quantity }},
                                            purchase_price: {{ $product->purchase_price }},
                                            selling_price: {{ $product->selling_price }},
                                            description: '{{ addslashes($product->description ?? '') }}',
                                            picture: '{{ $product->picture }}',
                                            edit_url: '{{ route('products.edit', $product) }}'
                                        })">
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                @if(isset($isSearching) && $isSearching)
                                    <i class="bi bi-search fs-1 text-muted"></i>
                                    <p class="mt-3 mb-2">No parts found matching your search criteria.</p>
                                    <p class="text-muted small">Try adjusting your search terms or filters.</p>
                                @else
                                    <i class="bi bi-box fs-1 text-muted"></i>
                                    <p class="mt-3 mb-2">No parts available.</p>
                                    <p class="text-muted small">Add some parts to get started.</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination with Info -->
            @if(isset($isSearching) && $isSearching)
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $products->count() }} search results
                        @if($products->count() >= 100)
                            <span class="text-warning">(showing first 100 matches)</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                    </div>
                    <div>
                {{ $products->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Single Product Modal (moved outside the table to prevent conflicts) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="productImage">
                            <div class="bg-light text-center p-5 rounded">
                                <i class="bi bi-image fs-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Part Number:</strong> <span id="modalPartNumber"></span></p>
                        <p><strong>Category:</strong> <span id="modalCategory"></span></p>
                        <p><strong>Brand:</strong> <span id="modalBrand"></span></p>
                        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                        <p><strong>Quantity:</strong> <span id="modalQuantity"></span></p>
                        <p><strong>Purchase Price:</strong> $<span id="modalPurchasePrice"></span></p>
                        <p><strong>Selling Price:</strong> $<span id="modalSellingPrice"></span></p>
                        <p><strong>Description:</strong></p>
                        <p id="modalDescription"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="modalEditBtn">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script>
function showProductModal(product) {
    // Update modal title
    document.getElementById('productModalLabel').textContent = product.name;
    
    // Update product image
    const imageContainer = document.getElementById('productImage');
    if (product.picture) {
        imageContainer.innerHTML = `<img src="{{ asset('storage/') }}/${product.picture}" alt="${product.name}" class="img-fluid rounded">`;
    } else {
        imageContainer.innerHTML = `
            <div class="bg-light text-center p-5 rounded">
                <i class="bi bi-image fs-1"></i>
            </div>
        `;
    }
    
    // Update product details
    document.getElementById('modalPartNumber').textContent = product.part_number;
    document.getElementById('modalCategory').textContent = product.category;
    document.getElementById('modalBrand').textContent = product.brand;
    document.getElementById('modalLocation').textContent = product.location;
    document.getElementById('modalQuantity').textContent = product.quantity;
    document.getElementById('modalPurchasePrice').textContent = product.purchase_price.toFixed(2);
    document.getElementById('modalSellingPrice').textContent = product.selling_price.toFixed(2);
    document.getElementById('modalDescription').textContent = product.description || 'No description available';
    
    // Update edit button link
    document.getElementById('modalEditBtn').href = product.edit_url;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

// Auto-search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const brandSelect = document.getElementById('brandSelect');
    const searchForm = document.getElementById('searchForm');
    const searchLoader = document.getElementById('searchLoader');
    
    let searchTimer;
    let lastSearchValue = searchInput.value;
    let isSearching = false;
    
    // Function to show/hide loading spinner
    function showLoader() {
        if (!isSearching) {
            searchLoader.style.display = 'block';
            isSearching = true;
        }
    }
    
    function hideLoader() {
        searchLoader.style.display = 'none';
        isSearching = false;
    }
    
    // Function to submit form with delay
    function submitFormWithDelay() {
        clearTimeout(searchTimer);
        showLoader();
        searchTimer = setTimeout(function() {
            searchForm.submit();
        }, 300); // Reduced delay to 300ms for better responsiveness
    }
    
    // Auto-submit on text input (with delay)
    searchInput.addEventListener('input', function() {
        const currentValue = this.value.trim();
        
        // Only trigger search if value actually changed
        if (currentValue !== lastSearchValue) {
            lastSearchValue = currentValue;
            
            // If input is empty, submit immediately to clear results
            if (currentValue === '') {
                showLoader();
                searchForm.submit();
            } else {
                submitFormWithDelay();
            }
        }
    });
    
    // Auto-submit on dropdown change (immediate)
    categorySelect.addEventListener('change', function() {
        showLoader();
        searchForm.submit();
    });
    
    brandSelect.addEventListener('change', function() {
        showLoader();
        searchForm.submit();
    });
    
    // Hide loader when page loads (in case of browser back button)
    window.addEventListener('load', function() {
        hideLoader();
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Focus search input when pressing '/'
        if (e.key === '/' && !searchInput.matches(':focus') && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        // Clear search when pressing Escape (if search input is focused)
        if (e.key === 'Escape' && searchInput.matches(':focus')) {
            if (searchInput.value) {
                searchInput.value = '';
                lastSearchValue = '';
                showLoader();
                searchForm.submit();
            } else {
                searchInput.blur();
            }
        }
    });
});
</script>
@endpush 