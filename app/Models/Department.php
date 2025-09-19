<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'kode'];
    protected $guarded = [''];
    protected $ignoreChangedAttributes = ['updated_at'];

}


