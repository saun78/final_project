<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .receipt-info {
            margin-bottom: 30px;
        }
        
        .info-row {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .receipt-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: 12px 8px;
            border: 1px solid #333;
        }
        
        .receipt-table td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #333;
        }
        
        .receipt-table .product-name {
            text-align: left;
        }
        
        .receipt-total {
            border-top: 2px solid #333;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .total-table {
            width: 300px;
            margin-left: auto;
        }
        
        .total-table td {
            padding: 8px;
            border: none;
        }
        
        .total-row {
            font-size: 16px;
            font-weight: bold;
        }
        
        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <div class="receipt-title">RECEIPT</div>
        <div>{{ $order->order_number }}</div>
    </div>

    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Receipt #:</span>
            {{ $order->order_number }}
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            {{ $order->created_at->format('M d, Y') }}
        </div>
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            @switch($order->payment_method)
                @case('cash')
                    Cash
                    @break
                @case('bank_transfer')
                    Bank Transfer
                    @break
                @case('tng_wallet')
                    TNG Wallet
                    @break
                @default
                    {{ $order->payment_method ?? 'Not specified' }}
            @endswitch
        </div>
        <div class="info-row">
            <span class="info-label">Total Items:</span>
            {{ $order->orderItems->sum('quantity') }}
        </div>
    </div>

    <table class="receipt-table">
        <thead>
            <tr>
                <th style="width: 40%">Product/Part</th>
                <th style="width: 20%">Supplier</th>
                <th style="width: 10%">Qty</th>
                <th style="width: 15%">Unit Price</th>
                <th style="width: 15%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td class="product-name">
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product_part_number)
                            <br><small>Part #: {{ $item->product_part_number }}</small>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <small>{{ $item->supplier_contact_person }}</small>
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="receipt-total">
        <table class="total-table">
            @php
                $itemsSubtotal = $order->orderItems->sum(function($item) {
                    return $item->quantity * $item->price;
                });
            @endphp
            <tr>
                <td><strong>Items Subtotal:</strong></td>
                <td style="text-align: right;">${{ number_format($itemsSubtotal, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Labor Fee:</strong></td>
                <td style="text-align: right;">${{ number_format($order->labor_fee, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>TOTAL:</strong></td>
                <td style="text-align: right;"><strong>${{ number_format($order->amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="receipt-footer">
        <p>Thank you for your business!</p>
        <p>{{ $order->created_at->format('l, F j, Y') }}</p>
    </div>
</body>
</html> 