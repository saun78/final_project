<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductInventory;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $query = Order::with('orderItems.product');

        // Filter by specific date if provided (from date picker)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // If no date filter, show all orders (or you can limit to recent orders)
            // For performance, you might want to limit to last 30 days
            $query->whereDate('created_at', '>=', now()->subDays(30));
        }

        // Get all orders sorted by date desc
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Group orders by date
        $ordersByDate = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        });

        // Get unique dates and paginate them (3 days per page)
        $dates = collect($ordersByDate->keys());
        $currentPage = request()->get('page', 1);
        $perPage = 3; // 3 days per page
        $offset = ($currentPage - 1) * $perPage;
        $currentDates = $dates->slice($offset, $perPage);

        // Get orders for current dates only
        $currentOrdersByDate = $ordersByDate->filter(function($orders, $date) use ($currentDates) {
            return $currentDates->contains($date);
        });

        // Manual pagination
        $total = $dates->count();
        $lastPage = ceil($total / $perPage);
        
        $pagination = [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'has_more_pages' => $currentPage < $lastPage,
            'prev_page_url' => $currentPage > 1 ? request()->fullUrlWithQuery(['page' => $currentPage - 1]) : null,
            'next_page_url' => $currentPage < $lastPage ? request()->fullUrlWithQuery(['page' => $currentPage + 1]) : null,
        ];

        return view('orders.index', compact('currentOrdersByDate', 'pagination'));
    }

    public function create()
    {
        $products = Product::all();
        return view('orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,tng_wallet',
            'labor_fee' => 'nullable|numeric|min:0'
        ]);

        // Check stock availability for all items first (using batch inventory)
        $productIds = collect($request->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        
        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                return redirect()->back()
                    ->withErrors(['stock' => "Product not found."])
                    ->withInput();
            }
            
            // Check real stock from batch inventory
            $availableStock = ProductInventory::getTotalStock($item['product_id']);
            if ($availableStock < $item['quantity']) {
                return redirect()->back()
                    ->withErrors(['stock' => "Insufficient stock for {$product->name}. Available: {$availableStock}, Required: {$item['quantity']}"])
                    ->withInput();
            }
        }

        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // Calculate total amount (including labor fee)
        $itemsTotal = 0;
        foreach ($request->items as $item) {
            $itemsTotal += $item['quantity'] * $item['price'];
        }
        
        $laborFee = $request->labor_fee ?? 0;
        $totalAmount = $itemsTotal + $laborFee;

        // Create order
        $order = Order::create([
            'order_number' => $orderNumber,
            'amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'labor_fee' => $laborFee
        ]);

        // Create order items and update stock using FIFO (using transaction)
        try {
            DB::transaction(function () use ($request, $order, $products) {
                foreach ($request->items as $item) {
                    $product = $products->get($item['product_id']);
                    
                    // 在事务内再次检查库存，防止竞态条件
                    $availableStock = ProductInventory::getTotalStock($item['product_id']);
                    if ($availableStock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableStock}, Required: {$item['quantity']}");
                    }
                    
                    // Create order item first to get ID for batch tracking
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'cost_price' => 0, // Will be updated after FIFO calculation
                        'product_name' => $product->name,
                        'product_part_number' => $product->part_number,
                        'supplier_contact_person' => $product->supplier?->contact_person,
                    ]);

                    // Deduct stock using FIFO method with batch tracking
                    try {
                        $batchDeductions = ProductInventory::deductFIFO($item['product_id'], $item['quantity'], $orderItem->id);
                        
                        // Calculate and update average cost
                        $totalCost = collect($batchDeductions)->sum('cost');
                        $averageCost = $totalCost / $item['quantity'];
                        
                        $orderItem->update(['cost_price' => $averageCost]);
                        
                    } catch (\Exception $e) {
                        throw new \Exception("Failed to deduct stock for {$product->name}: " . $e->getMessage());
                    }
                }
            });
        } catch (\Exception $e) {
            // 如果事务失败，删除已创建的订单并返回错误
            $order->delete();
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('orders.index')->with('success', 'Receipt created successfully! Product stock updated.');
    }

    public function show(Order $order)
    {
        $order->load('orderItems.product');
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $products = Product::all();
        $order->load('orderItems.product');
        return view('orders.edit', compact('order', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,tng_wallet',
            'labor_fee' => 'nullable|numeric|min:0'
        ]);

        // Update only payment method and labor fee (non-inventory fields)
        // Items cannot be changed to maintain batch inventory integrity
        $order->update([
            'payment_method' => $request->payment_method,
            'labor_fee' => $request->labor_fee ?? 0,
            'amount' => $order->orderItems->sum(function($item) {
                return $item->quantity * $item->price;
            }) + ($request->labor_fee ?? 0)
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Order payment details updated successfully.');
    }

    public function destroy(Order $order)
    {
        // DANGEROUS OPERATION: This restores inventory and deletes the order
        // Use with extreme caution - this should only be used for correcting immediate mistakes
        DB::transaction(function () use ($order) {
            // Restore inventory to original batches
            foreach ($order->orderItems as $item) {
                $product = $item->product;
                if ($product) {
                    // Get the sales movements for this order item to know which batches were used
                    $salesMovements = \App\Models\InventoryMovement::getOrderItemBatches($item->id);
                    
                    if ($salesMovements->isEmpty()) {
                        // Fallback: if no movement records found, create a new batch (old behavior)
                        $costPrice = $item->cost_price ?: $product->purchase_price;
                        $batch = $product->addStock(
                            $item->quantity,
                            $costPrice,
                            now()->toDateString(),
                            'ORDER-CANCEL-' . $order->order_number,
                            'Inventory restored from cancelled order #' . $order->order_number . ' (no original batch records found)'
                        );
                    } else {
                        // Restore inventory to original batches
                        foreach ($salesMovements as $movement) {
                            $originalBatch = \App\Models\ProductInventory::find($movement->batch_id);
                            
                            if ($originalBatch) {
                                // Restore the quantity to the original batch
                                $quantityToRestore = abs($movement->quantity); // Movement quantity is negative for sales
                                $originalBatch->quantity += $quantityToRestore;
                                
                                // If the batch was marked as depleted, reactivate it
                                if ($originalBatch->status === 'depleted') {
                                    $originalBatch->status = 'active';
                                    $originalBatch->depleted_date = null;
                                }
                                
                                $originalBatch->save();
                                
                                // Record the restoration movement
                                \App\Models\InventoryMovement::recordReturn(
                                    $product->id,
                                    $originalBatch->id,
                                    $originalBatch->batch_no,
                                    $quantityToRestore,
                                    $movement->unit_cost,
                                    $order->order_number
                                );
                            }
                        }
                        
                        // Mark the original sales movements as cancelled
                        foreach ($salesMovements as $movement) {
                            $movement->update([
                                'notes' => $movement->notes . ' [CANCELLED - Order #' . $order->order_number . ' deleted]'
                            ]);
                        }
                    }
                }
            }
            
            $order->orderItems()->delete();
            $order->delete();
        });

        return redirect()->route('orders.index')->with('warning', 'Order deleted and inventory restored to original batches. Use this feature only for correcting immediate mistakes.');
    }

    public function print(Order $order)
    {
        $order->load('orderItems.product');
        return view('orders.print', compact('order'));
    }

    public function exportPdf(Request $request)
    {
        $query = Order::with('orderItems.product');

        // Apply date filter if provided (single date from date picker)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // If no date provided, use today's date
            $query->whereDate('created_at', now()->format('Y-m-d'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $selectedDate = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $formattedDate = \Carbon\Carbon::parse($selectedDate)->format('M_d_Y');

        $pdf = Pdf::loadView('orders.pdf', compact('orders', 'selectedDate'))
                  ->setPaper('a4', 'portrait');

        $filename = 'receipts_' . $formattedDate . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = Order::with('orderItems.product');

        // Apply date filter if provided (single date from date picker)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            // If no date provided, use today's date
            $query->whereDate('created_at', now()->format('Y-m-d'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $selectedDate = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $formattedDate = \Carbon\Carbon::parse($selectedDate)->format('M_d_Y');

        // Create new spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Receipts_' . $formattedDate);

        // Add report title and date
        $sheet->setCellValue('A1', 'RECEIPTS REPORT');
        $sheet->mergeCells('A1:G1');
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        $sheet->setCellValue('A2', 'Report for ' . \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y'));
        $sheet->mergeCells('A2:G2');
        $dateStyle = [
            'font' => ['italic' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A2')->applyFromArray($dateStyle);

        // Set headers
        $headers = ['Order Number', 'Payment Method', 'Product Name', 'Part Number', 'Quantity', 'Unit Price', 'Subtotal', 'Labor Fee', 'Order Total'];
        $sheet->fromArray($headers, null, 'A4');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

        // Add data
        $row = 5;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $index => $item) {
                $paymentMethod = '';
                if ($index === 0) {
                    switch ($order->payment_method) {
                        case 'cash':
                            $paymentMethod = 'Cash';
                            break;
                        case 'card':
                            $paymentMethod = 'Card';
                            break;
                        case 'tng_wallet':
                            $paymentMethod = 'TNG Wallet';
                            break;
                        default:
                            $paymentMethod = $order->payment_method ?? 'N/A';
                    }
                }
                
                $data = [
                    $index === 0 ? $order->order_number : '',
                    $paymentMethod,
                    $item->product->name ?? 'N/A',
                    $item->product->part_number ?? '',
                    $item->quantity,
                    $item->price,
                    $item->quantity * $item->price,
                    $index === 0 ? ($order->labor_fee ?? 0) : '',
                    $index === 0 ? $order->amount : ''
                ];
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }
            
            // Add order total row for visual separation
            $totalRowData = [
                '', '', '', '', 'Order Total (' . $order->orderItems->sum('quantity') . ' items):', '', '', ($order->labor_fee ?? 0), $order->amount
            ];
            $sheet->fromArray($totalRowData, null, 'A' . $row);
            
            // Style the total row with more prominent separation
            $totalRowStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D6D6D6']  // Darker gray for better separation
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['rgb' => '888888']
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['rgb' => '888888']
                    ]
                ]
            ];
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($totalRowStyle);
            $row++;
            
            // Add empty row for visual separation
            $row++;
        }

        // Format price columns as currency
        $sheet->getStyle('E5:I' . ($row - 1))->getNumberFormat()
              ->setFormatCode('$#,##0.00');

        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders to data area
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A4:I' . ($row - 1))->applyFromArray($borderStyle);

        // Add summary at the end
        $summaryStartRow = $row + 2;
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('amount');
        $totalItems = $orders->sum(function($order) { return $order->orderItems->sum('quantity'); });

        $sheet->setCellValue('A' . $summaryStartRow, 'SUMMARY');
        $sheet->mergeCells('A' . $summaryStartRow . ':I' . $summaryStartRow);
        $sheet->getStyle('A' . $summaryStartRow)->applyFromArray($titleStyle);

        $summaryStartRow++;
        $sheet->setCellValue('A' . $summaryStartRow, 'Total Orders:');
        $sheet->setCellValue('B' . $summaryStartRow, $totalOrders);
        $summaryStartRow++;
        $sheet->setCellValue('A' . $summaryStartRow, 'Total Items Sold:');
        $sheet->setCellValue('B' . $summaryStartRow, $totalItems);
        $summaryStartRow++;
        $sheet->setCellValue('A' . $summaryStartRow, 'Total Amount:');
        $sheet->setCellValue('B' . $summaryStartRow, '$' . number_format($totalAmount, 2));

        // Style summary section
        $summaryStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A' . ($summaryStartRow - 2) . ':B' . $summaryStartRow)->applyFromArray($summaryStyle);

        $filename = 'receipts_' . $formattedDate . '.xlsx';

        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printSingle(Order $order)
    {
        $order->load('orderItems.product');
        
        $pdf = Pdf::loadView('orders.print-single', compact('order'))
                  ->setPaper('a4', 'portrait');

        $filename = 'receipt_' . $order->order_number . '.pdf';
        
        return $pdf->download($filename);
    }
} 