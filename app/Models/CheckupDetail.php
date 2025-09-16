<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckupDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'general_checkup_id',
        'check_item_id',
        'item_status',
        'maintenance_notes',
    ];

    /**
     * Relasi ke GeneralCheckup
     */
    public function generalCheckup()
    {
        return $this->belongsTo(GeneralCheckup::class);
    }

    /**
     * Relasi ke CheckItem
     */
    public function checkItem()
    {
        return $this->belongsTo(CheckItem::class);
    }

    /**
     * Relasi ke CheckupStandards
     */
    public function standards()
    {
        return $this->hasMany(CheckupStandard::class);
    }
}