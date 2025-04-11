<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'driver_id',
        'type',
        'description',
        'amount',
        'image',
    ];

    protected $dates = ['deleted_at'];
}
