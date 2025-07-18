<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class rawDistributions extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'raw_stock_transactions_id',
        'plant_id',
        'quantity',
        'status',
        'accepted_at',
    ];

    protected $dates = ['deleted_at', 'accepted_at'];

      public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function transaction()
    {
        return $this->belongsTo(rawStockTransactions::class, 'raw_stock_transactions_id');
    }
}
