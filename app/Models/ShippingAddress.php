<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\Contracts;
use App\Models\Drivers;
use App\Models\Routes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ShippingAddress extends Authenticatable
{
    use HasApiTokens, HasFactory, softDeletes;
    // use HasFactory;
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
        'contact_person',
        'contact_person_phone',
        'machine_deployed',
        'machine_deployed_date'
    ];
    protected $hidden = ['password'];


    public function Customers()
    {
        return $this->hasMany(Customers::class, 'customer_id');
    }

    public function Contract()
    {
        return $this->belongsTo(Contracts::class, 'contract_id');
    }

    protected $dates = ['deleted_at'];


}
