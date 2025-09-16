<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_request_id',
        'photo_path',
        'photo_description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Relasi ke RepairRequest
     */
    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }

    /**
     * Get full photo URL
     */
    public function getPhotoUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }
}