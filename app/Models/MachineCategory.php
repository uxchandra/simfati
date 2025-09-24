<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineCategory extends Model
{
    use HasFactory;

    protected $table = 'machine_categories';

    protected $fillable = ['name'];

    public function machines()
    {
        return $this->hasMany(Machine::class, 'category_id');
    }
}

