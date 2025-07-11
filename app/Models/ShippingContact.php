<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class ShippingContact extends Authenticatable
{
    use HasApiTokens, HasFactory, softDeletes;
    protected $fillable = [
        'shipping_id',
        'customer_id',
        'name',
        'phone',
    ];

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }

    public function shippingContactMultiples()
{
    return $this->hasMany(ShippingContactsMultiple::class, 'shipping_contacts_id');
}

    protected $hidden = ['password'];
    protected $dates = ['deleted_at'];


}
