<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
          'for',
           'platform',
            'latest_version',
            'min_supported_version',
            'store_url'
    ];
}
