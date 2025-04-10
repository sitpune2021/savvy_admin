<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class Pincode extends Model
{
    use HasFactory;
    protected $fillable = ['code', 'city_id'];

    public function city() {
        return $this->belongsTo(City::class);
    }
}
