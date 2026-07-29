<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Distributor extends Authenticatable
{
    use HasApiTokens, HasFactory , SoftDeletes;
    protected $fillable = [
        'zoho_id',
        'name',
        'email',
        'password',
        'phone_no',
        'full_address',
        'country',
        'state',
        'city',
        'pincode',
        'po_no',
        'license_no',
        'tempo_no',
        'tempo_name',
        'pan_card',
        'aadhar_card',
        'pan_card_FILE',
        'aadhar_card_FILE'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(DistributorPlantOrder::class);
    }

    public function plants()
    {
        return $this->belongsToMany(
            Plant::class,
            'distributor_plant_allocations',
            'distributor_id',
            'plant_id'
        )->withTimestamps();
    }

}
