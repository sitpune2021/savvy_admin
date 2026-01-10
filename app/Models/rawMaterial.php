<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rawMaterial extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function variants()
    {
        return $this->hasMany(rawMaterialVariants::class);
    }

}
