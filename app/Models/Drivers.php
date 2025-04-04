<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drivers extends Model
{
    use HasFactory , SoftDeletes;

        protected $fillable = [
            'name',
            'email',
            'phone_no',
            'full_address',
            'country',
            'state',
            'city',
            'pincode',
            'license_no',
            'vehicle_no',
            'vehicle_name',
            'pan_card',
            'aadhar_card',
            'pan_card_FILE',
            'aadhar_card_FILE',
        ];
    

    protected $dates = ['deleted_at'];
}
