<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Drivers;
use App\Models\Plant;


class Routes extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'plant_id',
        'name',
        'path',
        'vendor_id',
    ];

    protected $dates = ['deleted_at'];

    public function drivers()
    {
        return $this->hasMany(Drivers::class, 'route_id');
    }

    public function Plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

}
