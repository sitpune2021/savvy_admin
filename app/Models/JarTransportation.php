<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JarTransportation extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'driver_id',
        'date',
        'status',
    ];

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
    
}
