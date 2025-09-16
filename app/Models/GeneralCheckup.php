<?php

// Model GeneralCheckup.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralCheckup extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkup_code',
        'machine_id',
        'part_id',
        'checkup_date',
        'user_id',
        'shift',
        'overall_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'checkup_date' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Relasi ke Machine
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Relasi ke Part
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Relasi ke User (Inspector)
     */
    public function inspector()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke User (Created By)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke CheckupDetails
     */
    public function details()
    {
        return $this->hasMany(CheckupDetail::class);
    }

    /**
     * Relasi ke CheckupPhotos
     */
    public function photos()
    {
        return $this->hasMany(CheckupPhoto::class);
    }

    /**
     * Generate checkup code automatically
     */
    public static function generateCheckupCode()
    {
        $date = date('Ymd');
        $lastCheckup = self::where('checkup_code', 'like', "CHK-{$date}-%")
                          ->orderBy('checkup_code', 'desc')
                          ->first();

        if ($lastCheckup) {
            $lastNumber = (int) substr($lastCheckup->checkup_code, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "CHK-{$date}-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checkup_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('overall_status', $status);
    }
}