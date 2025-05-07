<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
use App\Models\ShippingAddress;

class MachineDispensary extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'model_name',
        'serial_number',
        'machine_type',
        'customer_id',
        'shipping_id',
        'documents',
        'warranty',
        'garanty',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function shipping()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }

    protected $dates = ['deleted_at'];

}
