@extends('layouts.app')

@section('title', 'Add New Receipt')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
.select2-container--bootstrap4 .select2-selection--single {
    height: calc(2.25rem + 2px) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    padding: 0.375rem 0.75rem;
    line-height: 1.5;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: calc(2.25rem + 2px) !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add New Receipt</h4>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Receipts
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                        @csrf
                        
                        <div class="mb-4">
                            <h5>Order Items</h5>
                            <div id="orderItems">
                                <div class="order-item row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Product/Part</label>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                        data-price="{{ $product->selling_price }}" 
                                                        data-stock="{{ $product->quantity }}"
                                                        data-part-number="{{ $product->part_number ?? '' }}">
                                                    @if($product->part_number)
                                                        [{{ $product->part_number }}] {{ $product->name }} - ${{ $product->selling_price }} (Stock: {{ $product->quantity }})
                                                    @else
                                                        {{ $product->name }} - ${{ $product->selling_price }} (Stock: {{ $product->quantity }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" 
                                               min="1" value="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Selling Price</label>
                                        <input type="number" name="items[0][price]" class="form-control price-input" 
                                               step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal-display" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="button" class="btn btn-danger btn-sm remove-item" style="display:none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" id="addItem" class="btn btn-success btn-sm">
                                <i class="bi bi-plus"></i> Add Item
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Order Summary</h5>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total Amount:</strong>
                                            <strong id="totalAmount">$0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Create Receipt
                                </button>
                                <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
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
// Check if jQuery is already loaded, if not load it
if (typeof jQuery === 'undefined') {
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = 1;
    
    function initializeSelect2(selectElement) {
        $(selectElement).select2({
            theme: 'bootstrap4',
            placeholder: 'Search product name or part number...',
            allowClear: true,
            width: '100%',
            matcher: function(params, data) {
                // Always return data if no search term
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Don't match the placeholder
                if (!data.id) {
                    return null;
                }

                var text = data.text || '';
                var $elem = $(data.element);
                var partNumber = $elem.attr('data-part-number') || '';
                
                var searchTerm = params.term.toLowerCase();
                
                // Search in text and part number
                if (text.toLowerCase().indexOf(searchTerm) > -1 || 
                    partNumber.toLowerCase().indexOf(searchTerm) > -1) {
                    return data;
                }

                return null;
            }
        });
    }
    
    function updateSubtotal(row) {
        const quantity = parseFloat($(row).find('.quantity-input').val()) || 0;
        const price = parseFloat($(row).find('.price-input').val()) || 0;
        const subtotal = quantity * price;
        $(row).find('.subtotal-display').val('$' + subtotal.toFixed(2));
        updateTotal();
    }
    
    function updateTotal() {
        let total = 0;
        $('.order-item').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            total += quantity * price;
        });
        $('#totalAmount').text('$' + total.toFixed(2));
    }
    
    function attachEventListeners(row) {
        const $row = $(row);
        const $productSelect = $row.find('select[name*="product_id"]');
        const $quantityInput = $row.find('.quantity-input');
        const $priceInput = $row.find('.price-input');
        const $removeBtn = $row.find('.remove-item');
        
        // Initialize Select2 for new selects
        if (!$productSelect.hasClass('select2-hidden-accessible')) {
            initializeSelect2($productSelect[0]);
        }
        
        $productSelect.on('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.price) {
                $priceInput.val(selectedOption.dataset.price);
                // Set max quantity based on stock
                if (selectedOption.dataset.stock) {
                    $quantityInput.attr('max', selectedOption.dataset.stock);
                    $quantityInput.attr('title', 'Available stock: ' + selectedOption.dataset.stock);
                }
                updateSubtotal(row);
            }
        });
        
        $quantityInput.on('input', () => updateSubtotal(row));
        $priceInput.on('input', () => updateSubtotal(row));
        
        $removeBtn.on('click', function() {
            $row.remove();
            updateTotal();
            updateRemoveButtons();
        });
    }
    
    function updateRemoveButtons() {
        const items = $('.order-item');
        items.each(function() {
            const $removeBtn = $(this).find('.remove-item');
            if (items.length > 1) {
                $removeBtn.show();
            } else {
                $removeBtn.hide();
            }
        });
    }
    
    // Add item functionality
    $('#addItem').on('click', function() {
        const $container = $('#orderItems');
        const $originalItem = $container.find('.order-item:first');
        const $newItem = $originalItem.clone(false);
        
        // Remove Select2 classes and data
        $newItem.find('.select2-container').remove();
        $newItem.find('select').removeClass('select2-hidden-accessible').removeData('select2');
        
        // Update field names and reset values
        $newItem.find('input, select').each(function() {
            const $field = $(this);
            if ($field.attr('name')) {
                $field.attr('name', $field.attr('name').replace(/\[\d+\]/, '[' + itemIndex + ']'));
            }
            if ($field.is('select')) {
                $field.val('');
            } else if ($field.hasClass('quantity-input')) {
                $field.val('1');
            } else if (!$field.is('[type="button"]')) {
                $field.val('');
            }
        });
        
        $container.append($newItem);
        attachEventListeners($newItem[0]);
        itemIndex++;
        updateRemoveButtons();
    });
    
    // Initialize first item
    $('.order-item').each(function() {
        attachEventListeners(this);
    });
    
    updateRemoveButtons();
});
</script>
@endpush 