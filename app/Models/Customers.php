<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Contracts;
use App\Models\ShippingAddress;
use App\Models\Plant;
use App\Models\Orders;


class Customers extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone_no',
        'billing_address',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_pincode',
        'customer_zohi_id',
        'plant_id',
    ];

    // Define the relationship with the Contracts model
    public function contracts()
    {
        return $this->hasMany(Contracts::class, 'customer_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'customer_id');
    }
    
    protected $dates = ['deleted_at'];
}
