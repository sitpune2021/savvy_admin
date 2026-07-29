<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGenerationFailure extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'customer_id', 'shipping_id', 'failure_date',
        'source', 'reason', 'details', 'attempted_at',
    ];

    protected $casts = [
        'failure_date' => 'date',
        'attempted_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contracts::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function shipping()
    {
        return $this->belongsTo(ShippingAddress::class);
    }
}
