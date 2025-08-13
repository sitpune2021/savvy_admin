<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Drivers;

class JarTransportation extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'driver_id',
        'date',
        'status', // 'dispatching', 'receiving', 'received'
        'total_quantity',
        'allocated_quantity',
        'allocat_quantity',
    ];

    public function JarLogs()
    {
        return $this->hasMany(JarTransportLog::class, 'jar_transportation_id');
    }

    public function Driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }
    
}
