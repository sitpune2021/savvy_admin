<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\rawMaterialVariants;


class rawStockTransactions extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'raw_material_variant_id',
        'type',
        'quantity',
    ];

    protected $dates = ['deleted_at'];

    public function variant()
    {
        return $this->belongsTo(rawMaterialVariants::class, 'raw_material_variant_id');
    }

    public function distributions()
    {
        return $this->hasMany(rawDistributions::class);
    }

}
