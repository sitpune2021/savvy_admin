<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Drivers;

class Maintenance extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'driver_id',
        'type',
        'description',
        'amount',
        'image',
        'status',
        'date'
    ];

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    protected $dates = ['deleted_at'];
}
