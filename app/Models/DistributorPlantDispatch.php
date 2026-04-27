<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorPlantDispatch extends Model
{
    use HasFactory;
        protected $fillable = [
        'distributor_plant_orders_id',
        'dispatched_labeled_jars',
        'dispatched_unlabeled_jars',
        'label_breakdown',
        'dispatched_at'
    ];

    protected $casts = [
        'label_breakdown' => 'array',
    ];
}
