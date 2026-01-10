<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Drivers;

class Maintenance extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = [
        'driver_id',
        'type',
        'description',
        'amount',
        'image',
        'status',
        'date',
    ];

    protected $casts = [
        'image' => 'array',
    ];
    

    public function driver()
    {
        return $this->belongsTo(Drivers::class, 'driver_id');
    }

    public function driverTrash()
    {
        return $this->belongsTo(Drivers::class, 'driver_id')->withTrashed();
    }

    public function getFuelLitersAttribute()
    {
        if (!$this->description) {
            return 0;
        }

        preg_match('/([\d\.]+)/', $this->description, $matches);
        return isset($matches[1]) ? (float) $matches[1] : 0;
    }

    protected $dates = ['deleted_at'];
}
