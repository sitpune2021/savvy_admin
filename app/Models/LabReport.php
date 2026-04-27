<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabReport extends Model
{

    protected $fillable = [
        'report_name',
        'title',
        'file_path',
        'version_no',
        'parent_id',
        'report_date',
        'expiry_date',
        'uploaded_by'
    ];

    protected $casts = [
        'report_date' => 'date',
        'expiry_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Parent report (main report)
    public function parent()
    {
        return $this->belongsTo(LabReport::class, 'parent_id');
    }

    // All versions of this report
    public function versions()
    {
        return $this->hasMany(LabReport::class, 'parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY USEFUL)
    |--------------------------------------------------------------------------
    */

    // Only main reports
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    // Latest version first
    public function scopeLatestVersion($query)
    {
        return $query->orderByDesc('version_no');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (🔥 important)
    |--------------------------------------------------------------------------
    */

    // Get all versions (including self)
    public function allVersions()
    {
        return self::where('id', $this->id)
            ->orWhere('parent_id', $this->id)
            ->orderByDesc('version_no');
    }

    // Get latest version
    public function latestVersion()
    {
        return $this->allVersions()->first();
    }

    // Check expiry
    public function isExpired()
    {
        return $this->expiry_date && now()->gt($this->expiry_date);
    }
}
