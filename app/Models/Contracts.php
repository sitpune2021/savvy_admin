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
