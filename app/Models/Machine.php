<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_code',
        'machine_name',
        'section',
        'status',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    // public function parts()
    // {
    //     return $this->hasMany(Part::class);
    // }

    // public function schedules()
    // {
    //     return $this->hasMany(Schedule::class, 'target_id')
    //                 ->where('target_type', 'machine');
    // }

    public function checkItems()
    {
        return $this->hasMany(CheckItem::class);
    }

    


}