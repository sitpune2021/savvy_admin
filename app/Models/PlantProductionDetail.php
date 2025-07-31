<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantProductionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_production_id',
        'raw_material_id',
        'quantity',
    ];


}
