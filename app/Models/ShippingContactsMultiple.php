<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingContactsMultiple extends Model
{
    use HasFactory, softDeletes;
    protected $fillable = [
        'shipping_id',
        'shipping_contacts_id',
        'mode'
    ];

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }


    public function shippingContact()
    {
        return $this->belongsTo(ShippingContact::class, 'shipping_contacts_id');
    }
    

    protected $dates = ['deleted_at'];


}
