@extends('layouts.app')

@section('title', 'Movement Details')

@push('styles')
<style>
.movement-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.movement-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 1.5rem;
}

.info-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid #667eea;
}

.info-section h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.85rem;
}

.info-value {
    font-weight: 600;
    color: #495057;
}

.payment-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.payment-cash { background: #d4edda; color: #155724; }
.payment-bank { background: #cce5ff; color: #004085; }
.payment-tng { background: #fff3cd; color: #856404; }

.quantity-display {
    font-size: 1.1rem;
    font-weight: 700;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    display: inline-block;
}

.quantity-in { background: #d4edda; color: #155724; }
.quantity-out { background: #f8d7da; color: #721c24; }

.notes-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 10px;
    padding: 1rem;
    margin-top: 1rem;
}

.back-btn {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    transform: translateY(-1px);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card movement-card">
                <div class="card-header movement-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="bi bi-arrow-left-right me-2"></i>
                            Movement Details
                        </h4>
                        <small class="opacity-75">{{ $movement->movement_date->format('l, F j, Y') }}</small>
                    </div>
                    <a href="{{ route('dashboard', ['page' => request('page')]) }}" class="btn back-btn">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    
                    
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-section">
                                <h6><i class="bi bi-info-circle me-2"></i>Movement Info</h6>
                                <div class="info-row">
                                    <span class="info-label">Type</span>
                                    <span class="info-value">
                                        @if($movement->movement_type == 'stock_in')
                                            <span class="badge bg-success">📥 In</span>
                                        @elseif($movement->movement_type == 'sale')
                                            <span class="badge bg-danger">📤 Out</span>
                                        @elseif($movement->movement_type == 'stock_out')
                                            <span class="badge bg-warning">📤 Out</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($movement->movement_type) }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Quantity</span>
                                    <span class="info-value">
                                        @if($movement->quantity > 0)
                                            <span class="quantity-display quantity-in">+{{ $movement->quantity }}</span>
                                        @else
                                            <span class="quantity-display quantity-out">{{ $movement->quantity }}</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Time</span>
                                    <span class="info-value">{{ $movement->movement_date->format('H:i') }}</span>
                                </div>
                                @if($movement->batch_no)
                                <div class="info-row">
                                    <span class="info-label">Batch</span>
                                    <span class="info-value">{{ $movement->batch_no }}</span>
                                </div>
                                @endif
                                @if($movement->reference_type && $movement->reference_id)
                                <div class="info-row">
                                    <span class="info-label">Order</span>
                                    <span class="info-value">#{{ $movement->reference_id }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-section">
                                <h6><i class="bi bi-box me-2"></i>Product Info</h6>
                                <div class="info-row">
                                    <span class="info-label">Product</span>
                                    <span class="info-value">{{ $movement->product->name ?? 'N/A' }}</span>
                                </div>
                                @if($movement->product->part_number)
                                <div class="info-row">
                                    <span class="info-label">Part #</span>
                                    <span class="info-value">{{ $movement->product->part_number }}</span>
                                </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-label">Supplier</span>
                                    <span class="info-value">{{ $movement->product->supplier->contact_person ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Current Stock</span>
                                    <span class="info-value">{{ $movement->product->quantity ?? 0 }}</span>
                                </div>
                                @if($movement->movement_type == 'stock_in')
                                <div class="info-row">
                                    <span class="info-label">Unit Cost</span>
                                    <span class="info-value">
                                        @if($movement->unit_cost !== null)
                                            RM{{ number_format($movement->unit_cost, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Total In Amount</span>
                                    <span class="info-value">
                                        @if($movement->unit_cost !== null)
                                            RM{{ number_format(abs($movement->quantity) * $movement->unit_cost, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                                @endif
                                @if($movement->reference_type == 'order_item' && $movement->reference_id)
                                    @php
                                        $orderItem = \App\Models\OrderItem::with('order')->find($movement->reference_id);
                                        $paymentMethod = $orderItem ? $orderItem->order->payment_method : null;
                                    @endphp
                                    @if($paymentMethod)
                                    <div class="info-row">
                                        <span class="info-label">Payment</span>
                                        <span class="info-value">
                                            @switch($paymentMethod)
                                                @case('cash')
                                                    <span class="payment-badge payment-cash">💵 Cash</span>
                                                    @break
                                                @case('bank')
                                                    <span class="payment-badge payment-bank">🏦 Bank</span>
                                                    @break
                                                @case('tng_wallet')
                                                    <span class="payment-badge payment-tng">📱 TNG</span>
                                                    @break
                                                @default
                                                    <span class="payment-badge bg-secondary">{{ ucfirst($paymentMethod) }}</span>
                                            @endswitch
                                        </span>
                                    </div>
                                    @endif
                                    @if($movement->movement_type == 'stock_in')
                                    <div class="info-row">
                                        <span class="info-label">Unit Cost</span>
                                        <span class="info-value">RM{{ number_format($movement->unit_cost, 2) }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Total In Amount</span>
                                        <span class="info-value">RM{{ number_format(abs($movement->quantity) * $movement->unit_cost, 2) }}</span>
                                    </div>
                                    @elseif($movement->movement_type == 'sale' && $orderItem)
                                    <div class="info-row">
                                        <span class="info-label">Total Sale Amount</span>
                                        <span class="info-value">RM{{ number_format($orderItem->quantity * $orderItem->price, 2) }}</span>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($movement->notes)
                    <div class="notes-section">
                        <h6 class="mb-2"><i class="bi bi-chat-text me-2"></i>Notes</h6>
                        <p class="mb-0">{{ $movement->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 