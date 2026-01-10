<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarTransportLog extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'jar_transportation_id',
        'action', // Action can be  'dispatching', 'receiving', 'received'
        'date',
        'quantity',
        'stocks',
    ];

    public function jarTransportation()
    {
        return $this->belongsTo(JarTransportation::class, 'jar_transportation_id');
    }

    public function jarTransportList()
    {
        return $this->hasMany(JarTransportDriverLog::class, 'jar_transport_log_id');
    }

    protected $casts = [
        'stocks' => 'array', // Assuming stocks is stored as a JSON array
        'date' => 'date', // Cast date to Carbon instance
    ];
    protected $dates = ['deleted_at'];


}
