<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class rawStockLogs extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'raw_material_id',
        'user_id',
        'plant_id',
        'action',
        'quantity',
        'note',
        'action_time'
    ];
    
    protected $dates = ['deleted_at'];
}
