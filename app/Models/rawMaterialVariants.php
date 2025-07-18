<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\rawStockTransactions;

class rawMaterialVariants extends Model
{
    use HasFactory;
    protected $fillable = ['raw_material_id', 'variant_name', 'total_quantity'];

    public function rawMaterial()
    {
        return $this->belongsTo(rawMaterial::class);
    }

    public function transactions()
    {
        return $this->hasMany(rawStockTransactions::class, 'raw_material_variant_id');
    }

    public function scopeWithRawMaterialName($query, $name)
    {
        return $query->whereHas('rawMaterial', function ($q) use ($name) {
            $q->where('name', $name);
        });
    }

    public function scopeWithVariantName($query, $variantName)
    {
        return $query->where('variant_name', $variantName);
    }

    public function scopeWithTotalQuantity($query, $quantity)
    {
        return $query->where('total_quantity', $quantity);
    }

    public function scopeWithRawMaterialId($query, $rawMaterialId)
    {
        return $query->where('raw_material_id', $rawMaterialId);
    }

    
}
