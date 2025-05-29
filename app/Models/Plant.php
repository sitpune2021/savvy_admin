<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'manager',
        'manager_id',
        'location',
        'pincode',
        'details',
        'vendor_id',
    ];

    public function managerRecord()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    protected $dates = ['deleted_at'];

}
