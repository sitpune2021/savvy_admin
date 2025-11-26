<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Drivers;

class JarMaintance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plant_id',
        'driver_id',
        'date',
        'status',
        'qty',
        'type',
        'raw_material_variants_id'
    ];

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    protected $dates = ['deleted_at'];
}
