<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorPlantOrderAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_plant_orders_id',
        'received_labeled_jars',
        'received_unlabeled_jars',
        'damaged_jars',
        'remarks',
        'status',
        'accepted_at'
    ];
}
