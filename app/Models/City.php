<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\State;
use App\Models\Pincode;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'state_id'];

    public function state() {
        return $this->belongsTo(State::class);
    }

    public function pincodes() {
        return $this->hasMany(Pincode::class);
    }
}
