<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Contracts;


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
        'contact_person',
        'contact_person_phone',
        'machine_deployed',
        'machine_deployed_date',
        'customer_zohi_id',
        'plant_id',
    ];

    // Define the relationship with the Contracts model
    public function contracts()
    {
        return $this->hasMany(Contracts::class, 'customer_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }
    
    protected $dates = ['deleted_at'];
}
