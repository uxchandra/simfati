<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckupStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkup_detail_id',
        'check_standard_id',
        'result',
        'notes',
    ];

    /**
     * Relasi ke CheckupDetail
     */
    public function checkupDetail()
    {
        return $this->belongsTo(CheckupDetail::class);
    }

    /**
     * Relasi ke CheckStandard
     */
    public function checkStandard()
    {
        return $this->belongsTo(CheckStandard::class);
    }
}
