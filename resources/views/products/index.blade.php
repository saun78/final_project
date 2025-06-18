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
            <div class="d-flex gap-2">
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-grid"></i> Manage Categories
                </a>
                <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-tags"></i> Manage Brands
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar (Always Visible) -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" id="searchForm">
                <!-- Hidden inputs to preserve other filter values -->
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="brand" value="{{ request('brand') }}">
                <input type="hidden" name="location" value="{{ request('location') }}">
                <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                
                <div class="row">
                    <div class="col-12">
                        <label for="searchInput" class="form-label">Search Products</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control" placeholder="Search by part number, name, or description..." value="{{ request('search') }}" id="searchInput">
                            <div class="position-absolute top-50 end-0 translate-middle-y pe-3" id="searchLoader" style="display: none;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Searching...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Advanced Filters (Collapsible) -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-funnel"></i> Advanced Filters
                    @if(request('category') || request('brand') || request('location') || request('stock_status') || request('min_price') || request('max_price'))
                        <span class="badge bg-primary ms-2">Active</span>
                    @endif
                </h6>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse" id="filterToggleBtn">
                    <i class="bi bi-chevron-down" id="filterIcon"></i> 
                    <span id="filterText">Show Filters</span>
                </button>
            </div>
        </div>
        <div class="collapse" id="filterCollapse">
            <div class="card-body">
                <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                    <!-- Preserve search value -->
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <!-- Category and Brand Filters -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="categorySelect" class="form-label">Category</label>
                            <select name="category" class="form-select" id="categorySelect">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="brandSelect" class="form-label">Brand</label>
                            <select name="brand" class="form-select" id="brandSelect">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Location and Stock Status -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="locationFilter" class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" id="locationFilter" placeholder="Filter by location..." value="{{ request('location') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="stockStatus" class="form-label">Stock Status</label>
                            <select name="stock_status" class="form-select" id="stockStatus">
                                <option value="">All Stock Levels</option>
                                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock (> 0)</option>
                                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (1-10)</option>
                                <option value="good_stock" {{ request('stock_status') == 'good_stock' ? 'selected' : '' }}>Good Stock (> 10)</option>
                                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="minPrice" class="form-label">Min Price ($)</label>
                            <input type="number" name="min_price" class="form-control" id="minPrice" placeholder="0.00" step="0.01" value="{{ request('min_price') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="maxPrice" class="form-label">Max Price ($)</label>
                            <input type="number" name="max_price" class="form-control" id="maxPrice" placeholder="999.99" step="0.01" value="{{ request('max_price') }}">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Apply Filters
                        </button>
                        @if(request('category') || request('brand') || request('location') || request('stock_status') || request('min_price') || request('max_price'))
                        <a href="{{ route('products.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Clear Filters
                        </a>
                        @endif
                        <button type="button" class="btn btn-outline-secondary ms-auto" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="bi bi-chevron-up"></i> Hide Filters
                        </button>
                    </div>
                </form>
            </div>
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
            <!-- Products Grid -->
            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-12">
                    <div class="card h-100 product-card">
                        <!-- Product Image -->
                        <div class="card-img-container" style="height: 200px; overflow: hidden; position: relative;">
                            @if($product->picture)
                                <img src="{{ asset('storage/' . $product->picture) }}" 
                                     alt="{{ $product->name }}" 
                                     class="card-img-top" 
                                     style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
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
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted"
                                     style="cursor: pointer;"
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
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                            
                            <!-- Quantity Badge -->
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-{{ $product->quantity > 10 ? 'success' : ($product->quantity > 0 ? 'warning' : 'danger') }} px-2 py-1">
                                    {{ $product->quantity }} in stock
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body d-flex flex-column">
                            <!-- Part Number -->
                            <small class="text-muted mb-1">{{ $product->part_number }}</small>
                            
                            <!-- Product Name -->
                            <h6 class="card-title mb-2 fw-bold" style="min-height: 2.4rem; line-height: 1.2;">
                                {{ $product->name }}
                            </h6>
                            
                            <!-- Category & Brand -->
                            <div class="mb-2">
                                <span class="badge bg-info me-1">{{ $product->category ? $product->category->name : 'Unknown' }}</span>
                                <span class="badge bg-secondary">{{ $product->brand ? $product->brand->name : 'Unknown' }}</span>
                            </div>
                            
                            <!-- Location -->
                            @if($product->location)
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> {{ $product->location }}
                                </small>
                            </div>
                            @endif
                            
                            <!-- Prices -->
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted d-block">Purchase</small>
                                        <span class="fw-semibold">${{ number_format($product->purchase_price, 2) }}</span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Selling</small>
                                        <span class="fw-semibold text-success">${{ number_format($product->selling_price, 2) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <button type="button" class="btn btn-sm btn-outline-info flex-fill" 
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
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning flex-fill" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline flex-fill">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" 
                                            title="Delete" onclick="return confirm('Are you sure you want to delete this part?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        @if(isset($isSearching) && $isSearching)
                            <i class="bi bi-search fs-1 text-muted"></i>
                            <p class="mt-3 mb-2 h5">No parts found matching your search criteria.</p>
                            <p class="text-muted">Try adjusting your search terms or filters.</p>
                        @else
                            <i class="bi bi-box fs-1 text-muted"></i>
                            <p class="mt-3 mb-2 h5">No parts available.</p>
                            <p class="text-muted">Add some parts to get started.</p>
                        @endif
                    </div>
                </div>
                @endforelse
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

@push('styles')
<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: 1px solid #e3e6f0;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-img-container:hover img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .col-xl-4, .col-lg-4, .col-md-4, .col-sm-6 {
        margin-bottom: 1rem;
    }
}

/* Loading animation for images */
.card-img-top {
    transition: transform 0.3s ease;
}

/* Ensure consistent card heights */
.product-card {
    min-height: 400px;
}

/* Better responsive grid spacing */
.row.g-4 {
    --bs-gutter-x: 1.5rem;
    --bs-gutter-y: 1.5rem;
}
</style>
@endpush

@push('scripts')
<script>
// Filter collapse toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterCollapse = document.getElementById('filterCollapse');
    const filterIcon = document.getElementById('filterIcon');
    const filterText = document.getElementById('filterText');
    
    if (filterCollapse && filterIcon && filterText) {
        filterCollapse.addEventListener('show.bs.collapse', function() {
            filterIcon.className = 'bi bi-chevron-up';
            filterText.textContent = 'Hide Filters';
        });
        
        filterCollapse.addEventListener('hide.bs.collapse', function() {
            filterIcon.className = 'bi bi-chevron-down';
            filterText.textContent = 'Show Filters';
        });
        
        // Auto-open filters if any filter is active (excluding search)
        @if(request('category') || request('brand') || request('location') || request('stock_status') || request('min_price') || request('max_price'))
            new bootstrap.Collapse(filterCollapse, {show: true});
        @endif
    }
});

// Search functionality (for the main search bar)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    const searchLoader = document.getElementById('searchLoader');
    
    let lastSearchValue = searchInput ? searchInput.value : '';
    let isSearching = false;
    
    // Function to show/hide loading spinner
    function showSearchLoader() {
        if (!isSearching && searchLoader) {
            searchLoader.style.display = 'block';
            isSearching = true;
        }
    }
    
    function hideSearchLoader() {
        if (searchLoader) {
            searchLoader.style.display = 'none';
        }
        isSearching = false;
    }
    
    // Auto-focus search input if there's a search term (after page load)
    if (searchInput && searchInput.value.trim() !== '') {
        setTimeout(function() {
            searchInput.focus();
            // Move cursor to end of text
            const length = searchInput.value.length;
            searchInput.setSelectionRange(length, length);
        }, 100);
    }
    
    // Auto-submit on search input (no delay)
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const currentValue = this.value.trim();
            
            if (currentValue !== lastSearchValue) {
                lastSearchValue = currentValue;
                showSearchLoader();
                searchForm.submit();
            }
        });
    }
    
    // Hide loader when page loads
    window.addEventListener('load', function() {
        hideSearchLoader();
    });
});

function showProductModal(product) {
    // Update modal title
    document.getElementById('productModalLabel').textContent = product.name;
    
    // Update product image
    const imageContainer = document.getElementById('productImage');
    if (product.picture) {
        imageContainer.innerHTML = `<img src="/storage/${product.picture}" alt="${product.name}" class="img-fluid rounded">`;
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


</script>
@endpush 