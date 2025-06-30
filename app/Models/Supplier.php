<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'name',
        'contact_person',
        'contact_number',
        'address',
        'slug'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($supplier) {
            $supplier->slug = Str::slug($supplier->name);
        });
        
        static::updating(function ($supplier) {
            $supplier->slug = Str::slug($supplier->name);
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
} 