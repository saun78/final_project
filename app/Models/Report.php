<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity_sold',
        'total_amount',
        'sale_date'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
} 