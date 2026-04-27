<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorPlantProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_plant_orders_id',
        'delivered_jars',
        'used_previous_stock',
        'total_available',
        'leak_jars',
        'green_jars',
        'usable_jars',
        'labeled_jars',
        'unlabeled_jars',
        'label_breakdown',
        'remaining_stock',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'label_breakdown' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(DistributorPlantOrder::class);
    }
}
