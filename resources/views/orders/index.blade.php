@extends('layouts.app')

@section('title', 'Receipts')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Receipt Management</h2>
                    <p class="text-muted mb-0">Manage and track all sales receipts</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Receipt
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('orders.export.pdf') }}?date={{ request('date') ?: \Carbon\Carbon::now()->format('Y-m-d') }}">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Export PDF
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.export.excel') }}?date={{ request('date') ?: \Carbon\Carbon::now()->format('Y-m-d') }}">
                                <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ request()->fullUrlWithQuery(['date' => request('date') ? \Carbon\Carbon::parse(request('date'))->subDay()->format('Y-m-d') : \Carbon\Carbon::now()->subDay()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-primary" id="prevDayBtn">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                        
                        <div class="text-center position-relative">
                            <h4 class="mb-0 fw-bold text-primary clickable-date" id="dateTitle" onclick="showDatePicker()" style="cursor: pointer;">
                                <i class="bi bi-calendar3 me-2"></i>
                                {{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('l, F j, Y') : \Carbon\Carbon::now()->format('l, F j, Y') }}
                                <i class="bi bi-chevron-down ms-2 small"></i>
                            </h4>
                            
                            <!-- Hidden date picker -->
                            <input type="date" 
                                   id="hiddenDatePicker" 
                                   class="form-control position-absolute"
                                   style="top: 100%; left: 50%; transform: translateX(-50%); width: 200px; z-index: 1000; display: none;"
                                   value="{{ request('date') ?: \Carbon\Carbon::now()->format('Y-m-d') }}"
                                   onchange="goToDate(this.value); hideDatePicker();"
                                   onblur="hideDatePicker()">
                        </div>
                        
                        <a href="{{ request()->fullUrlWithQuery(['date' => request('date') ? \Carbon\Carbon::parse(request('date'))->addDay()->format('Y-m-d') : \Carbon\Carbon::now()->addDay()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-primary" id="nextDayBtn">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-calendar-today me-1"></i> Today
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- System Notice -->
    {{-- <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle me-2 mt-1 text-warning"></i>
                    <div>
                        <strong>Limited Edit/Delete Functions:</strong> You can edit payment method and labor fee only. Product items cannot be changed to maintain batch inventory integrity. Deletion does NOT restore inventory - use only for correcting mistakes.
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            @php
                $currentDate = request('date') ? request('date') : \Carbon\Carbon::now()->format('Y-m-d');
                $currentOrders = $currentOrdersByDate->get($currentDate, collect());
            @endphp
            
            @if($currentOrders->isEmpty())
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-receipt display-1 text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Receipts Found</h4>
                        <p class="text-muted mb-4" id="emptyMessage">
                            @if(request('date'))
                                No receipts found for {{ \Carbon\Carbon::parse(request('date'))->format('F j, Y') }}.
                            @else
                                No receipts found for today. Start by creating your first receipt.
                            @endif
                        </p>
                        <a href="{{ route('orders.create') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i> Create Receipt
                        </a>
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-receipt text-primary me-2 fs-5"></i>
                                <h5 class="mb-0 fw-bold">Daily Sales Summary</h5>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-primary fs-6">{{ $currentOrders->count() }} receipts</span>
                                <span class="text-muted">Total: <strong class="text-success">${{ number_format($currentOrders->sum('amount'), 2) }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Receipt Number</th>
                                        <th class="border-0 fw-semibold">Items Summary</th>
                                        <th class="border-0 fw-semibold">Amount</th>
                                        <th class="border-0 fw-semibold">Date</th>
                                        <th class="border-0 fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentOrders as $order)
                                        <tr class="align-middle">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-receipt text-muted me-2"></i>
                                                    <strong class="text-dark">{{ $order->order_number }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-info me-2">{{ $order->orderItems->count() }} items</span>
                                                    <small class="text-muted">Total Qty: {{ $order->orderItems->sum('quantity') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success fs-6">${{ number_format($order->amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $order->created_at->format('M j, Y') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('orders.print', $order) }}" class="btn btn-outline-success btn-sm" target="_blank" title="Print">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <!-- Limited edit and delete functionality -->
                                                    <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-warning btn-sm" 
                                                       title="Edit payment method and labor fee only">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDelete({{ $order->id }}, '{{ $order->order_number }}')"
                                                            title="Delete order and restore inventory (Use with caution)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm to Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-trash display-4 text-danger"></i>
                </div>
                <p class="text-center mb-3">Are you sure you want to delete receipt:</p>
                <div class="text-center mb-4">
                    <strong class="fs-5" id="orderNumber"></strong>
                </div>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <small><strong>WARNING:</strong> This will delete the order and restore product inventory. This action cannot be undone. Use only to correct immediate mistakes.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete Receipt
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(orderId, orderNumber) {
    document.getElementById('orderNumber').textContent = orderNumber;
    document.getElementById('deleteForm').action = '/orders/' + orderId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Quick date navigation function
function goToDate(selectedDate) {
    if (selectedDate) {
        window.location.href = '{{ route("orders.index") }}?date=' + selectedDate;
    }
}

// Show/hide date picker functions
function showDatePicker() {
    const datePicker = document.getElementById('hiddenDatePicker');
    const dateTitle = document.getElementById('dateTitle');
    
    datePicker.style.display = 'block';
    datePicker.focus();
    
    // Change chevron direction
    const chevron = dateTitle.querySelector('.bi-chevron-down');
    if (chevron) {
        chevron.classList.remove('bi-chevron-down');
        chevron.classList.add('bi-chevron-up');
    }
}

function hideDatePicker() {
    setTimeout(() => {
        const datePicker = document.getElementById('hiddenDatePicker');
        const dateTitle = document.getElementById('dateTitle');
        
        datePicker.style.display = 'none';
        
        // Change chevron direction back
        const chevron = dateTitle.querySelector('.bi-chevron-up');
        if (chevron) {
            chevron.classList.remove('bi-chevron-up');
            chevron.classList.add('bi-chevron-down');
        }
    }, 150);
}

// Keyboard navigation for date switching
document.addEventListener('keydown', function(event) {
    // Ignore if user is typing in an input field
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        return;
    }
    
    if (event.key === 'ArrowLeft') {
        // Previous day
        event.preventDefault();
        const currentDate = '{{ request("date") ?: \Carbon\Carbon::now()->format("Y-m-d") }}';
        const prevDate = new Date(currentDate);
        prevDate.setDate(prevDate.getDate() - 1);
        const prevDateStr = prevDate.toISOString().split('T')[0];
        goToDate(prevDateStr);
    } else if (event.key === 'ArrowRight') {
        // Next day
        event.preventDefault();
        const currentDate = '{{ request("date") ?: \Carbon\Carbon::now()->format("Y-m-d") }}';
        const nextDate = new Date(currentDate);
        nextDate.setDate(nextDate.getDate() + 1);
        const nextDateStr = nextDate.toISOString().split('T')[0];
        goToDate(nextDateStr);
    } else if (event.key === 't' || event.key === 'T') {
        // Jump to today
        event.preventDefault();
        goToDate('{{ \Carbon\Carbon::now()->format("Y-m-d") }}');
    }
});

// Add visual feedback and enhanced functionality
document.addEventListener('DOMContentLoaded', function() {
    const prevBtn = document.getElementById('prevDayBtn');
    const nextBtn = document.getElementById('nextDayBtn');
    const datePicker = document.getElementById('quickDatePicker');
    
    if (prevBtn && nextBtn) {
        // Add tooltips to indicate keyboard shortcuts
        prevBtn.setAttribute('title', 'Previous Day (← Arrow Key)');
        nextBtn.setAttribute('title', 'Next Day (→ Arrow Key)');
    }
    
    // Add tooltip to date picker
    if (datePicker) {
        datePicker.setAttribute('title', 'Click to select any date');
    }
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add loading state for better UX
    function addLoadingState(element) {
        const originalContent = element.innerHTML;
        element.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i>Loading...';
        element.disabled = true;
        
        setTimeout(() => {
            element.innerHTML = originalContent;
            element.disabled = false;
        }, 1000);
    }
    
    // Add click handlers for loading states
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            addLoadingState(this);
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            addLoadingState(this);
        });
    }
});

// Add CSS for animations and styles
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .clickable-date {
        transition: all 0.2s ease;
        border-radius: 8px;
        padding: 8px 16px;
        margin: -8px -16px;
    }
    
    .clickable-date:hover {
        background-color: rgba(13, 110, 253, 0.1);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .clickable-date:active {
        transform: translateY(0);
    }
    
    #hiddenDatePicker {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 2px solid #0d6efd;
        border-radius: 8px;
    }
    
    #hiddenDatePicker:focus {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15), 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        border-color: #86b7fe;
        outline: none;
    }
`;
document.head.appendChild(style);
</script>
@endpush 