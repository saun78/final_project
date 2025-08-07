@extends('layouts.app')

@section('title', 'Suppliers Management')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.product-item {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.product-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.product-item.out-of-stock {
    opacity: 0.6;
    background-color: #f8f9fa;
}

.product-item .product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.product-item .product-details {
    font-size: 0.875rem;
    color: #6c757d;
}

.product-item .stock-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.products-loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.products-empty {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.products-empty i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
}

/* Sidebar Design */
.products-sidebar {
    position: fixed;
    top: 0;
    right: -400px; /* Fully hidden off-screen */
    width: 400px;
    height: 100vh;
    background-color: #ffffff;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.3);
    padding: 0;
    transition: right 0.4s ease-in-out;
    z-index: 1050;
    overflow-y: auto;
}

.products-sidebar.active {
    right: 0; /* Slides in from the right */
}

.products-sidebar .sidebar-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 20px;
    position: sticky;
    top: 0;
    z-index: 1;
}

.products-sidebar .sidebar-header h5 {
    margin: 0;
    color: #333;
    font-weight: 600;
}

.products-sidebar .sidebar-body {
    padding: 20px;
}

.products-sidebar .close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.products-sidebar .close-btn:hover {
    background-color: #e9ecef;
    color: #495057;
}

/* Overlay for sidebar */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .products-sidebar {
        width: 100%;
        right: -100%;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Suppliers Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-grid"></i> Manage Categories
            </a>
            <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-tags"></i> Manage Brands
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Suppliers Section -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Suppliers</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                    <i class="bi bi-plus-lg"></i> Add Supplier
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-3">
                <div class="d-flex align-items-center">
                    <div class="position-relative flex-grow-1" style="max-width: 500px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="supplierSearch" class="form-control ps-5" placeholder="Search suppliers..." 
                               value="{{ request('search') }}">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    @endif
                </div>
            </div>

            <!-- Search Results Info -->
            <div class="mb-3" id="searchInfo" style="display: {{ request('search') ? 'block' : 'none' }};">
                <span class="text-muted" id="searchText">
                    @if(request('search'))
                        Search results for "<strong>{{ request('search') }}</strong>" - {{ $suppliers->count() }} result(s) found
                    @endif
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="suppliersTableBody">
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $supplier->contact_number }}</td>
                            <td><span class="badge bg-info">{{ $supplier->products_count }}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="showSupplierProducts({{ $supplier->id }}, '{{ addslashes($supplier->contact_person) }}')"
                                        title="View Products">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="editSupplier({{ $supplier->id }}, '{{ addslashes($supplier->contact_person) }}', '{{ addslashes($supplier->contact_number) }}')"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            title="Delete" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                @if(request('search'))
                                    No suppliers found matching your search.
                                @else
                                    No suppliers found.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Products Sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="hideProductsSidebar()"></div>
<div class="products-sidebar" id="productsSidebar">
    <div class="sidebar-header">
        <h5 id="sidebarTitle">Supplier Products</h5>
        <button type="button" class="close-btn" onclick="hideProductsSidebar()">
            <i class="bi bi-x"></i>
        </button>
    </div>
    <div class="sidebar-body">
        <div id="productsList">
            <!-- Products will be loaded here -->
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                               id="contact_person" name="contact_person" required 
                               placeholder="e.g. John Smith" value="{{ old('contact_person') }}">
                        @error('contact_person')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">Only letters and spaces are allowed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" 
                               id="contact_number" name="contact_number" required 
                               placeholder="e.g. +1-234-567-8900" value="{{ old('contact_number') }}">
                        @error('contact_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                               id="edit_contact_person" name="contact_person" required value="{{ old('contact_person') }}">
                        @error('contact_person')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">Only letters and spaces are allowed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control @error('contact_number') is-invalid @enderror" 
                               id="edit_contact_number" name="contact_number" required value="{{ old('contact_number') }}">
                        @error('contact_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editSupplier(id, contactPerson, contactNumber) {
    document.getElementById('edit_contact_person').value = contactPerson;
    document.getElementById('edit_contact_number').value = contactNumber;
    document.getElementById('editSupplierForm').action = `/suppliers/${id}`;
    new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
}

function showSupplierProducts(supplierId, supplierName) {
    // Show the products sidebar
    const productsSidebar = document.getElementById('productsSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarTitle = document.getElementById('sidebarTitle');
    const productsList = document.getElementById('productsList');
    
    // Update title
    sidebarTitle.textContent = `${supplierName} Products`;
    
    // Show loading state
    productsList.innerHTML = `
        <div class="products-loading">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            Loading products...
        </div>
    `;
    
    // Show the sidebar and overlay
    productsSidebar.classList.add('active');
    sidebarOverlay.classList.add('active');
    
    // Prevent body scroll when sidebar is open
    document.body.style.overflow = 'hidden';
    
    // Fetch products via AJAX
    fetch(`/suppliers/${supplierId}/products`)
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                let productsHtml = '';
                data.products.forEach(product => {
                    const stockClass = product.quantity <= 0 ? 'out-of-stock' : '';
                    const stockBadgeClass = product.quantity > 10 ? 'bg-success' : 
                                           product.quantity > 0 ? 'bg-warning' : 'bg-danger';
                    const stockText = product.quantity <= 0 ? 'Out of Stock' : `${product.quantity} in stock`;
                    
                    productsHtml += `
                        <div class="product-item ${stockClass}">
                            <div class="product-name">${product.name}</div>
                            <div class="product-details">
                                <div>Part Number: ${product.part_number || 'N/A'}</div>
                                <div>Category: ${product.category ? product.category.name : 'N/A'}</div>
                                <div>Brand: ${product.brand ? product.brand.name : 'N/A'}</div>
                                <div>Price: $${product.selling_price}</div>
                                <div class="mt-2">
                                    <span class="badge ${stockBadgeClass} stock-badge">${stockText}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                productsList.innerHTML = productsHtml;
            } else {
                productsList.innerHTML = `
                    <div class="products-empty">
                        <i class="bi bi-box"></i>
                        <div>No products found for this supplier</div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            productsList.innerHTML = `
                <div class="products-empty">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>Error loading products</div>
                </div>
            `;
        });
}

function hideProductsSidebar() {
    const productsSidebar = document.getElementById('productsSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Hide the sidebar and overlay
    productsSidebar.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    
    // Restore body scroll
    document.body.style.overflow = '';
}

// Auto search functionality with AJAX
let searchTimeout;
const searchInput = document.getElementById('supplierSearch');
const tableBody = document.getElementById('suppliersTableBody');
const searchInfo = document.getElementById('searchInfo');
const searchText = document.getElementById('searchText');

// Auto focus on search box after page load
document.addEventListener('DOMContentLoaded', function() {
    if (searchInput.value.length > 0) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
    
    // Add keyboard event listener for ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            hideProductsSidebar();
        }
    });
    
    // Show modal if there are validation errors
    @if($errors->any())
        if (document.querySelector('.is-invalid')) {
            new bootstrap.Modal(document.getElementById('addSupplierModal')).show();
        }
    @endif
});

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(this.value);
        }, 300);
    });
}

function performSearch(query) {
    const url = new URL(window.location.href);
    url.searchParams.set('search', query);
    
    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTable(data.suppliers);
        updateSearchInfo(data.search, data.count);
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

function updateTable(suppliers) {
    let html = '';
    
    if (suppliers.length === 0) {
        html = `<tr><td colspan="4" class="text-center">
            ${searchInput.value ? 'No suppliers found matching your search.' : 'No suppliers found.'}
        </td></tr>`;
    } else {
        suppliers.forEach(supplier => {
            html += `<tr>
                <td>${supplier.contact_person}</td>
                <td>${supplier.contact_number}</td>
                <td><span class="badge bg-info">${supplier.products_count}</span></td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                            onclick="editSupplier(${supplier.id}, '${supplier.contact_person.replace(/'/g, "\\'")}', '${supplier.contact_number.replace(/'/g, "\\'")}')"
                            title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="/suppliers/${supplier.id}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>`;
        });
    }
    
    tableBody.innerHTML = html;
}

function updateSearchInfo(searchQuery, count) {
    if (searchQuery && searchQuery.trim() !== '') {
        searchText.innerHTML = `Search results for "<strong>${searchQuery}</strong>" - ${count} result(s) found`;
        searchInfo.style.display = 'block';
    } else {
        searchInfo.style.display = 'none';
    }
}
</script>
@endpush 