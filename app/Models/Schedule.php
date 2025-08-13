<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule'; // If you want to use 'schedules', change this accordingly

    protected $fillable = [
        'name',
        'contact_num',
        'appointment_date',
        'appointment_time',
        'description',
        'location',
    ];
} 