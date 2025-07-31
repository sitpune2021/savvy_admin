<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'production_date',
        'quantity',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

}
