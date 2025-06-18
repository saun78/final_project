@extends('layouts.app')

@section('title', 'Brands Management')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Brands Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-grid"></i> Manage Categories
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

    <!-- Brands Section -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Brands</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="bi bi-plus-lg"></i> Add Brand
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="mb-3">
                <div class="d-flex align-items-center">
                    <div class="position-relative flex-grow-1" style="max-width: 500px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="brandSearch" class="form-control ps-5" placeholder="Search brands..." 
                               value="{{ request('search') }}">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    @endif
                </div>
            </div>

            <!-- Search Results Info -->
            <div class="mb-3" id="searchInfo" style="display: {{ request('search') ? 'block' : 'none' }};">
                <span class="text-muted" id="searchText">
                    @if(request('search'))
                        Search results for "<strong>{{ request('search') }}</strong>" - {{ $brands->count() }} result(s) found
                    @endif
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="brandsTableBody">
                        @forelse($brands as $brand)
                        <tr>
                            <td>{{ $brand->name }}</td>
                            <td><span class="badge bg-info">{{ $brand->products_count }}</span></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="editBrand({{ $brand->id }}, '{{ addslashes($brand->name) }}')"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('brands.destroy', $brand) }}" method="POST" class="d-inline">
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
                            <td colspan="3" class="text-center">
                                @if(request('search'))
                                    No brands found matching your search.
                                @else
                                    No brands found.
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

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('brands.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Brand Name</label>
                        <input type="text" class="form-control" id="brand_name" name="name" required 
                            placeholder="e.g. Honda">
                        <div class="form-text">Only letters, numbers, and spaces are allowed.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Brand Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBrandForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_brand_name" class="form-label">Brand Name</label>
                        <input type="text" class="form-control" id="edit_brand_name" name="name" required>
                        <div class="form-text">Only letters, numbers, and spaces are allowed.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editBrand(id, name) {
    document.getElementById('edit_brand_name').value = name;
    document.getElementById('editBrandForm').action = `/brands/${id}`;
    new bootstrap.Modal(document.getElementById('editBrandModal')).show();
}

// Auto search functionality with AJAX
let searchTimeout;
const searchInput = document.getElementById('brandSearch');
const tableBody = document.getElementById('brandsTableBody');
const searchInfo = document.getElementById('searchInfo');
const searchText = document.getElementById('searchText');

// Auto focus on search box after page load
document.addEventListener('DOMContentLoaded', function() {
    if (searchInput.value.length > 0) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
});

searchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const searchValue = e.target.value;
    
    searchTimeout = setTimeout(() => {
        performSearch(searchValue);
    }, 300); // 减少延迟到300ms，提高响应速度
});

function performSearch(searchValue) {
    // 显示加载状态
    tableBody.innerHTML = '<tr><td colspan="3" class="text-center"><i class="bi bi-spinner-border" role="status"></i> Searching...</td></tr>';
    
    // 使用fetch API进行AJAX请求
    const url = new URL(window.location.href);
    url.searchParams.set('search', searchValue);
    
    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateTable(data.brands);
        updateSearchInfo(data.search, data.count);
        updateURL(data.search);
    })
    .catch(error => {
        console.error('Search error:', error);
        tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Search failed. Please try again.</td></tr>';
    });
}

function updateTable(brands) {
    if (brands.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No brands found matching your search.</td></tr>';
        return;
    }
    
    let html = '';
    brands.forEach(brand => {
        html += `
            <tr>
                <td>${brand.name}</td>
                <td><span class="badge bg-info">${brand.products_count}</span></td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                            onclick="editBrand(${brand.id}, '${brand.name.replace(/'/g, "\\'")}')"
                            title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="/brands/${brand.id}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-outline-danger btn-sm" 
                                title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        `;
    });
    tableBody.innerHTML = html;
}

function updateSearchInfo(search, count) {
    if (search && search.trim() !== '') {
        searchText.innerHTML = `Search results for "<strong>${search}</strong>" - ${count} result(s) found`;
        searchInfo.style.display = 'block';
    } else {
        searchInfo.style.display = 'none';
    }
}

function updateURL(search) {
    const url = new URL(window.location);
    if (search && search.trim() !== '') {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    window.history.replaceState({}, '', url.toString());
}
</script>
@endpush 