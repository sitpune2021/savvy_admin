<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\Drivers;


class Orders extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'driver_id',
        'quantity',
        'order_details'
    ];

    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function drivers()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    protected $dates = ['deleted_at'];
}
