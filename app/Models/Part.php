<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_code',
        'part_name',
        'machine_id',
        'model',
        'process',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke Machine
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function checkItems()
    {
        return $this->hasMany(CheckItem::class);
    }

}
