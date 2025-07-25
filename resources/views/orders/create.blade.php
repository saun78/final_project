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
.supplier-display {
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6;
    color: #495057;
    font-weight: 500;
}
.supplier-display::placeholder {
    color: #6c757d;
    font-style: italic;
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
                                    <div class="col-md-3">
                                        <label class="form-label">Product/Part</label>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                        data-price="{{ $product->selling_price }}" 
                                                        data-stock="{{ $product->quantity }}"
                                                        data-part-number="{{ $product->part_number ?? '' }}"
                                                        data-category="{{ $product->category->name ?? 'N/A' }}"
                                                        data-brand="{{ $product->brand->name ?? 'N/A' }}"
                                                        data-supplier="{{ $product->supplier->contact_person ?? 'N/A' }}"
                                                        data-image="{{ $product->picture ? asset('storage/' . $product->picture) : '' }}">
                                                    @if($product->part_number)
                                                        [{{ $product->part_number }}] {{ $product->name }} - ${{ $product->selling_price }} ({{ $product->category->name ?? 'N/A' }}/{{ $product->brand->name ?? 'N/A' }}/{{ $product->supplier->contact_person ?? 'N/A' }}) (Stock: {{ $product->quantity }})
                                                    @else
                                                        {{ $product->name }} - ${{ $product->selling_price }} ({{ $product->category->name ?? 'N/A' }}/{{ $product->brand->name ?? 'N/A' }}/{{ $product->supplier->contact_person ?? 'N/A' }}) (Stock: {{ $product->quantity }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" class="form-control supplier-display bg-light" readonly placeholder="Select product first">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" 
                                               min="1" value="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Selling Price</label>
                                        <input type="number" name="items[0][price]" class="form-control price-input" 
                                               step="0.01" min="0" required readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal-display" readonly>
                                    </div>
                                    <div class="col-md-1">
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
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5>Payment Details</h5>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Payment Method</label>
                                            <select name="payment_method" class="form-select" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="cash">Cash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="tng_wallet">TNG Wallet</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Labor Fee</label>
                                            <input type="number" name="labor_fee" id="laborFee" class="form-control" 
                                                   step="0.01" min="0" value="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5>Order Summary</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotalAmount">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Labor Fee:</span>
                                            <span id="laborFeeDisplay">$0.00</span>
                                        </div>
                                        <hr>
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

<!-- Hidden template for new items - moved outside form -->
<div id="itemTemplate" style="display: none;">
    <div class="order-item row mb-3">
        <div class="col-md-3">
            <label class="form-label">Product/Part</label>
            <select name="items[INDEX][product_id]" class="form-select product-select" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" 
                            data-price="{{ $product->selling_price }}" 
                            data-stock="{{ $product->quantity }}"
                            data-part-number="{{ $product->part_number ?? '' }}"
                            data-category="{{ $product->category->name ?? 'N/A' }}"
                            data-brand="{{ $product->brand->name ?? 'N/A' }}"
                            data-supplier="{{ $product->supplier->contact_person ?? 'N/A' }}"
                            data-image="{{ $product->picture ? asset('storage/' . $product->picture) : '' }}">
                        @if($product->part_number)
                            [{{ $product->part_number }}] {{ $product->name }} - ${{ $product->selling_price }} ({{ $product->category->name ?? 'N/A' }}/{{ $product->brand->name ?? 'N/A' }}/{{ $product->supplier->contact_person ?? 'N/A' }}) (Stock: {{ $product->quantity }})
                        @else
                            {{ $product->name }} - ${{ $product->selling_price }} ({{ $product->category->name ?? 'N/A' }}/{{ $product->brand->name ?? 'N/A' }}/{{ $product->supplier->contact_person ?? 'N/A' }}) (Stock: {{ $product->quantity }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Supplier</label>
            <input type="text" class="form-control supplier-display bg-light" readonly placeholder="Select product first">
        </div>
        <div class="col-md-2">
            <label class="form-label">Quantity</label>
            <input type="number" name="items[INDEX][quantity]" class="form-control quantity-input" 
                   min="1" value="1" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Selling Price</label>
            <input type="number" name="items[INDEX][price]" class="form-control price-input" 
                   step="0.01" min="0" required readonly>
        </div>
        <div class="col-md-2">
            <label class="form-label">Subtotal</label>
            <input type="text" class="form-control subtotal-display" readonly>
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <div>
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="bi bi-trash"></i>
                </button>
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
    
    // Initialize Select2 configuration
    function initializeSelect2(element) {
        $(element).select2({
            theme: 'bootstrap4',
            placeholder: 'Search product name or part number',
            allowClear: true,
            width: '100%',
            templateResult: formatProductOption,
            templateSelection: formatProductSelection,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') return data;
                if (!data.id) return null;

                var text = data.text || '';
                var $elem = $(data.element);
                var partNumber = $elem.attr('data-part-number') || '';
                var searchTerm = params.term.toLowerCase();
                
                if (text.toLowerCase().indexOf(searchTerm) > -1 || 
                    partNumber.toLowerCase().indexOf(searchTerm) > -1) {
                    return data;
                }
                return null;
            }
        });
    }
    
    function formatProductOption(product) {
        if (!product.id || !product.element) return product.text;
        
        var $product = $(product.element);
        var imageUrl = $product.data('image');
        var price = $product.data('price');
        var stock = $product.data('stock');
        var category = $product.data('category');
        var brand = $product.data('brand');
        var supplier = $product.data('supplier');
        var hasValidImage = imageUrl && imageUrl.trim() !== '' && imageUrl.includes('storage');
        
        var imageElement = hasValidImage ? 
            '<img src="' + imageUrl + '" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px; border-radius: 4px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';" />' : '';
        
        var placeholderElement = '<div style="width: 40px; height: 40px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin-right: 10px; display: ' + (hasValidImage ? 'none' : 'flex') + '; align-items: center; justify-content: center; font-size: 12px; color: #6c757d;"><i class="bi bi-image"></i></div>';
        
        var productName = product.text.split(' - ')[0];
        var categoryBrandSupplier = category + '/' + brand + '/' + supplier;
        
        return $('<div class="d-flex align-items-center">' +
                    imageElement + placeholderElement +
                    '<div>' +
                        '<div style="font-weight: 500;">' + productName + '</div>' +
                        '<small class="text-muted">$' + price + ' - ' + categoryBrandSupplier + ' - Stock: ' + stock + '</small>' +
                    '</div>' +
                '</div>');
    }
    
    function formatProductSelection(product) {
        if (!product.id || !product.element) return product.text;
        return product.text.split(' - ')[0];
    }
    
    function updateSubtotal(row) {
        const quantity = parseFloat($(row).find('.quantity-input').val()) || 0;
        const price = parseFloat($(row).find('.price-input').val()) || 0;
        const subtotal = quantity * price;
        $(row).find('.subtotal-display').val('$' + subtotal.toFixed(2));
        updateTotal();
    }
    
    function updateTotal() {
        let subtotal = 0;
        $('#orderItems .order-item').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            subtotal += quantity * price;
        });
        
        const laborFee = parseFloat($('#laborFee').val()) || 0;
        const total = subtotal + laborFee;
        
        $('#subtotalAmount').text('$' + subtotal.toFixed(2));
        $('#laborFeeDisplay').text('$' + laborFee.toFixed(2));
        $('#totalAmount').text('$' + total.toFixed(2));
    }
    
    function setupOrderItem(item) {
        const $item = $(item);
        const $select = $item.find('.product-select');
        const $quantity = $item.find('.quantity-input');
        const $price = $item.find('.price-input');
        const $supplier = $item.find('.supplier-display');
        const $remove = $item.find('.remove-item');
        
        // Initialize Select2
        initializeSelect2($select);
        
        // Product selection change
        $select.on('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.price) {
                $price.val(selectedOption.dataset.price);
                $supplier.val(selectedOption.dataset.supplier || 'N/A');
                if (selectedOption.dataset.stock) {
                    $quantity.attr('max', selectedOption.dataset.stock);
                    $quantity.attr('title', 'Available stock: ' + selectedOption.dataset.stock);
                }
                updateSubtotal(item);
            } else {
                // Clear fields when no product is selected
                $price.val('');
                $supplier.val('');
                $quantity.removeAttr('max').removeAttr('title');
            }
        });
        
        // Quantity and price changes
        $quantity.on('input', () => updateSubtotal(item));
        $price.on('input', () => updateSubtotal(item));
        
        // Remove item
        $remove.on('click', function() {
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $item.remove();
            updateTotal();
            updateRemoveButtons();
        });
    }
    
    function updateRemoveButtons() {
        const $items = $('#orderItems .order-item');
        $items.find('.remove-item').toggle($items.length > 1);
    }
    
    // Add new item using template
    $('#addItem').on('click', function() {
        const templateHtml = $('#itemTemplate').html();
        const newHtml = templateHtml.replace(/INDEX/g, itemIndex);
        
        $('#orderItems').append(newHtml);
        const $newItem = $('#orderItems .order-item:last');
        
        setupOrderItem($newItem);
        itemIndex++;
        updateRemoveButtons();
    });
    
    // Initialize existing items
    $('#orderItems .order-item').each(function() {
        setupOrderItem(this);
    });
    
    // Labor fee changes
    $('#laborFee').on('input', updateTotal);
    
    // Initial setup
    updateRemoveButtons();
});
</script>
@endpush 