<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;

class ShippingAddress extends Model
{
    // use HasFactory, softDeletes;
    use HasFactory;
    protected $fillable = [
        'customer_id',
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
    public function Customers()
    {
        return $this->hasMany(Customers::class, 'customer_id');
    }

    // protected $dates = ['deleted_at'];


}
