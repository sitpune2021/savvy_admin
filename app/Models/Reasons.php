<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reasons extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['for', 'reasons'];
    protected $dates = ['deleted_at'];

}
