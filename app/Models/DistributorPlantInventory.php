<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorPlantInventory extends Model
{
    use HasFactory;

       protected $fillable = [
        'plant_id',
        'distributor_id',
        'empty_jars',
        'filled_unlabeled_jars',
        'labeled_jars'
    ];

    protected $casts = [
        'labeled_jars' => 'array',
    ];

        public function plant()
        {
            return $this->belongsTo(Plant::class);
        }
}
