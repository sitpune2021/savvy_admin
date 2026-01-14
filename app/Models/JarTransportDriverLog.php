<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class JarTransportDriverLog extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'driver_id',
        'jar_transport_log_id',
        'action',
        'status',
        'remark',
    ];

    public function jarTransportLog()
    {
        return $this->belongsTo(JarTransportLog::class);
    }
}
