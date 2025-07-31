<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Orders;


class Drivers extends Authenticatable
{
    use HasApiTokens, HasFactory , SoftDeletes;

        protected $fillable = [
            'route_id',
            'plant_id',
            'route_path',
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
            'vendor_id',
        ];

    public function routes()
    {
        return $this->belongsTo(Routes::class, 'route_id');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'driver_id');
    }

    public function jarTransportation()
    {
        return $this->hasOne(JarTransportation::class, 'driver_id');
    }

    
    protected $hidden = ['otp','otp_expires_at' ];
    

    protected $dates = ['deleted_at'];
}
