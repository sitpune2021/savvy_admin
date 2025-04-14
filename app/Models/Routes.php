<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Drivers;


class Routes extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'name',
        'path',
    ];

    protected $dates = ['deleted_at'];

    public function drivers()
    {
        return $this->hasMany(Drivers::class, 'route_id');
    }

}
