<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckupPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_checkup_id',
        'photo_path',
        'photo_description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Relasi ke GeneralCheckup
     */
    public function generalCheckup()
    {
        return $this->belongsTo(GeneralCheckup::class);
    }

    /**
     * Get full photo URL
     */
    public function getPhotoUrlAttribute()
    {
        return asset('storage/' . $this->photo_path);
    }
}