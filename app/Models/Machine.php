<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'status',
        'kode',
        'description',
        'kapasitas',
        'model',
        'tahun_pembuatan',
        'nomor_seri',
        'power',
        'tgl_instal',
        'keterangan',
        'capacity_kn',
        'slide_stroke',
        'stroke_per_minute',
        'die_height',
        'slide_adjustment',
        'slide_area',
        'bolster_area',
        'main_motor',
        'req_air_pressure',
        'max_upper_die_weight',
        'power_source',
        'braking_time',
        'lane',
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'machine_user');
    }

    public function category()
    {
        return $this->belongsTo(MachineCategory::class, 'category_id');
    }
}