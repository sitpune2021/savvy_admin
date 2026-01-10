<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrabJar extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'qty',
        'amount'
    ];
}
