<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;

class Contracts extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
        'price',
        'delivery_frequency',
        'delivery_time',
        'duration',
        'duration_type',
        'status',
    ];

    public function customer()
    {
        return $this->hasMany(Customers::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected $dates = ['deleted_at'];

    
}
