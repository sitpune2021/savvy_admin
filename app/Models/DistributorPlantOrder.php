<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorPlantOrder extends Model
{
    use HasFactory;

     protected $fillable = [
        'plant_id',
        'distributor_id',
        'delivered_jars',
        'used_previous_stock',
        'required_labeled_jars',
        'required_unlabeled_jars',
        'jars_with_label',
        'allow_remaining_stock',
        'status',
        'approved_at'
    ];

    protected $casts = [
        'jars_with_label' => 'array',
        'allow_remaining_stock' => 'boolean',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }


    public function production()
    {
        return $this->hasOne(DistributorPlantProduction::class, 'distributor_plant_orders_id');
    }

    public function dispatch()
    {
        return $this->hasOne(DistributorPlantDispatch::class, 'distributor_plant_orders_id');
    }

    public function acceptance()
    {
        return $this->hasOne(DistributorPlantOrderAcceptance::class, 'distributor_plant_orders_id');
    }
}
