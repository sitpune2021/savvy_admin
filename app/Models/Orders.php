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
        'type',
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

    public function scopeForVendor($query, $vendorId, $isAdmin = false, $type = 'all')
    {
        if ($isAdmin) {
            return $query->whereHas('drivers', function ($q) use ($type) {
                if ($type === 'pan_india') {
                    $q->whereNotNull('vendor_id');
                } elseif ($type === 'local') {
                    $q->whereNull('vendor_id');
                } else {
                    $q;
                }
            });
        } else {
            if ($vendorId !== null) {
                return $query->whereHas('drivers', function ($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                });
            }
        }
        return $query;
    }

    public function scopeForPlantManager($query, $plantManagerId)
    {
        
            if ($plantManagerId !== null) {
                return $query->whereHas('shipping', function ($q) use ($plantManagerId) {
                    $q->where('plant_id', $plantManagerId);
                });
            }
        
        return $query;
    }



    protected $dates = ['deleted_at'];
}
