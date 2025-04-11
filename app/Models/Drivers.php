<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;



class Drivers extends Authenticatable
{
    use HasApiTokens, HasFactory , SoftDeletes;

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
            'otp', 'otp_expires_at',
        ];
    

    protected $dates = ['deleted_at'];
}
