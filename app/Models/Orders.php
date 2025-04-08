<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\Drivers;


class Orders extends Model
{
    // use HasFactory, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'driver_id',
        'status',
        'develivered_qty',
        'return_qty',
        'delevered_card_img',
        'return_card_img',

    ];

    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function drivers()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    // protected $dates = ['deleted_at'];
}
