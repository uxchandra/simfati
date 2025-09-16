<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'part_id',
        'item_name',
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
     * Relasi ke CheckStandards
     */
    public function standards()
    {
        return $this->hasMany(CheckStandard::class);
    }
}
