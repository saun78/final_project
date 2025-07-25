<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'name',
        'contact_person',
        'contact_number',
        'address',
    ];



    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
} 