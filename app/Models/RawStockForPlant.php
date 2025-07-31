<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\rawMaterialVariants;
use App\Models\Plant;

class RawStockForPlant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plant_id',
        'raw_material_variants_id',
        'total_quantity',
        'total_production_quantity'
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function rawMaterialVariant()
    {
        return $this->belongsTo(rawMaterialVariants::class, 'raw_material_variants_id');
    }

    protected $dates = ['deleted_at'];

}
