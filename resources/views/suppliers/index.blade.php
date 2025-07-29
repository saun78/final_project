@extends('layouts.app')

@section('title', 'Suppliers Management')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <input type="text" class="form-control" id="contact_person" name="contact_person" required 
                            placeholder="e.g. John Smith">
                        <div class="form-text">Only letters and spaces are allowed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="contact_number" name="contact_number" required 
                            placeholder="e.g. +1-234-567-8900">
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
                        <input type="text" class="form-control" id="edit_contact_person" name="contact_person" required>
                        <div class="form-text">Only letters and spaces are allowed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_number" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
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