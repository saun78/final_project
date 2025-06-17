<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipts Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .report-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-subtitle {
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-section {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #ccc;
        }
        
        .summary-row {
            margin-bottom: 8px;
        }
        
        .summary-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .orders-table th {
            background-color: #333;
            color: white;
            font-weight: bold;
            padding: 10px 6px;
            border: 1px solid #333;
            font-size: 10px;
        }
        
        .orders-table td {
            padding: 8px 6px;
            border: 1px solid #ccc;
            font-size: 10px;
        }
        
        .orders-table .order-number {
            font-weight: bold;
        }
        
        .orders-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .date-section {
            margin-bottom: 25px;
        }
        
        .date-header {
            background-color: #e9ecef;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #333;
            margin-bottom: 0;
        }
        
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="report-title">RECEIPTS REPORT</div>
        <div class="report-subtitle">
            @if(isset($selectedDate))
                Report for {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
            @else
                Generated on {{ now()->format('M d, Y') }}
            @endif
        </div>
        <div class="report-subtitle">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</div>
    </div>

    @php
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('amount');
        $totalItems = $orders->sum(function($order) { return $order->orderItems->sum('quantity'); });
        $ordersByDate = $orders->groupBy(function($order) { return $order->created_at->format('Y-m-d'); });
    @endphp

    <div class="summary-section">
        <h3 style="margin-top: 0;">Summary</h3>
        <div class="summary-row">
            <span class="summary-label">Total Orders:</span>
            {{ $totalOrders }}
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Amount:</span>
            ${{ number_format($totalAmount, 2) }}
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Items Sold:</span>
            {{ $totalItems }}
        </div>
        <div class="summary-row">
            <span class="summary-label">Date Range:</span>
            @if(isset($selectedDate))
                {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
            @else
                {{ $orders->min('created_at')->format('M d, Y') }} - {{ $orders->max('created_at')->format('M d, Y') }}
            @endif
        </div>
    </div>

    @foreach($ordersByDate as $date => $dayOrders)
        <div class="date-section">
            <div class="date-header">
                {{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }} 
                ({{ $dayOrders->count() }} orders - ${{ number_format($dayOrders->sum('amount'), 2) }})
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th style="width: 25%">Order Number</th>
                        <th style="width: 45%">Items</th>
                        <th style="width: 15%">Quantity</th>
                        <th style="width: 15%">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayOrders as $order)
                        <tr>
                            <td class="order-number">{{ $order->order_number }}</td>
                            <td>
                                @foreach($order->orderItems as $item)
                                    {{ $item->product->name ?? 'N/A' }}@if(!$loop->last), @endif
                                @endforeach
                            </td>
                            <td style="text-align: center;">{{ $order->orderItems->sum('quantity') }}</td>
                            <td class="amount">${{ number_format($order->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="report-footer">
        <p>End of Report</p>
        <p>Generated: {{ now()->format('l, F j, Y') }}</p>
    </div>
</body>
</html> 