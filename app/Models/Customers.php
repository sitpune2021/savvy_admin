<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


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
        'shipping_address',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_pincode',
    ];
    protected $dates = ['deleted_at'];

}
