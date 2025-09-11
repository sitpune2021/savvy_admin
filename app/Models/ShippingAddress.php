<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\Contracts;
use App\Models\Drivers;
use App\Models\Routes;
use App\Models\ShippingContact;
use App\Models\ShippingContactsMultiple;
use App\Models\Plant;

class ShippingAddress extends Model
{
    use HasFactory, softDeletes;
    
    protected $fillable = [
        'customer_id',
        'contract_id',
        'plant_id',
        'route_id',
        'driver_id',
        'shipping_address',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_pincode',
        'machine_deployed',
        'machine_deployed_date',
        'type',
        'vendor_id',
    ];

    public function Customers()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function Contract()
    {
        return $this->belongsTo(Contracts::class, 'contract_id');
    }

    public function contacts()
    {
        return $this->hasMany(ShippingContactsMultiple::class, 'shipping_id');
    }

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function Plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    protected $dates = ['deleted_at'];


}
