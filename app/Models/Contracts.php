<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;
use App\Models\ShippingContact;
use App\Models\ShippingAddress;

use App\Models\Product;

class Contracts extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'customer_id',
        'product_id',
        'quantity',
        'price',
        'frequency',
        'frequency_count',
        'duration',
        'duration_type',
        'status',
        'days',
        'date',
        'send_by',
        'shipping_addresses_id',
        'accepted_status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sender(){
        return $this->belongsTo(ShippingContact::class, 'send_by');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_addresses_id');
    }

    protected $dates = ['deleted_at'];

    
}
