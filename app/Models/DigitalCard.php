<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Orders;
use App\Models\ShippingContact;

class DigitalCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'balance',
        'accept_by',
        'created_at',
        'updated_at',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function acceptBy()
    {
        return $this->belongsTo(ShippingContact::class,'accept_by' );
    }
    protected $dates = ['deleted_at'];

}
