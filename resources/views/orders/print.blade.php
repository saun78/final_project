<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .receipt-container { margin: 0; padding: 20px; }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            border: 1px solid #ddd;
            background: white;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-section {
            flex: 1;
        }
        
        .info-section h6 {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .receipt-table {
            margin-bottom: 30px;
        }
        
        .receipt-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: 15px 10px;
        }
        
        .receipt-table td {
            padding: 12px 10px;
            text-align: center;
        }
        
        .receipt-total {
            border-top: 2px solid #333;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .total-row {
            font-size: 18px;
            font-weight: bold;
        }
        
        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
        }
        
        .print-actions {
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="no-print print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <a href="{{ route('orders.print.pdf', $order) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Download PDF
        </a>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Receipt
        </a>
    </div>

    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-title">RECEIPT</div>
            <div class="text-muted">{{ $order->order_number }}</div>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h6>Receipt Information</h6>
                <p><strong>Receipt #:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
            </div>
            <div class="info-section">
                <h6>Summary</h6>
                <p><strong>Total Items:</strong> {{ $order->orderItems->sum('quantity') }}</p>
                <p><strong>Total Amount:</strong> ${{ number_format($order->amount, 2) }}</p>
            </div>
        </div>

        <table class="table table-bordered receipt-table">
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
                        <td style="text-align: left;">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->product_part_number)
                                <br><small class="text-muted">Part #: {{ $item->product_part_number }}</small>
                            @endif
                        </td>
                        <td>
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
            <div class="row">
                <div class="col-8"></div>
                <div class="col-4">
                    <table class="table table-borderless">
                        @php
                            $itemsSubtotal = $order->orderItems->sum(function($item) {
                                return $item->quantity * $item->price;
                            });
                        @endphp
                        <tr>
                            <td><strong>Items Subtotal:</strong></td>
                            <td class="text-end">${{ number_format($itemsSubtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Labor Fee:</strong></td>
                            <td class="text-end">${{ number_format($order->labor_fee, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>TOTAL:</strong></td>
                            <td class="text-end"><strong>${{ number_format($order->amount, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Thank you for your business!</p>
            <p><small>{{ $order->created_at->format('l, F j, Y') }}</small></p>
        </div>
    </div>

    <script>
        // Auto-focus for better print experience
        window.addEventListener('load', function() {
            document.body.focus();
        });
    </script>
</body>
</html> 