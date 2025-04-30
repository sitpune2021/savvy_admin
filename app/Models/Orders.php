<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\ShippingAddress;
use App\Models\Contracts;
use App\Models\Routes;


class Orders extends Model
{
    use HasFactory, SoftDeletes;
    // use HasFactory;

    protected $fillable = [
        'customer_id',
        'contract_id',
        'shipping_id',
        'route_id',
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

    
    public function contract()
    {
        return $this->belongsTo(Contracts::class, 'contract_id');
    }

    public function shipping()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }

    public function route()
    {
        return $this->belongsTo(Routes::class, 'route_id');
    }

    
    public function drivers()
    {
        
        return $this->belongsTo(Drivers::class, 'driver_id')->withTrashed();
    }


    protected $dates = ['deleted_at'];
}
