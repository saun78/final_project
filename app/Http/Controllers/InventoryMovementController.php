<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function show(InventoryMovement $movement)
    {
        $movement->load(['product.category', 'product.brand', 'product.supplier', 'batch']);
        $page = request()->get('page', 1); // 默认回到第1页
        return view('inventory-movements.show', compact('movement', 'page'));
    }
    
} 