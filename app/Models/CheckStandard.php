<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_item_id',
        'standard_name',
    ];

    /**
     * Relasi ke CheckItem
     */
    public function checkItem()
    {
        return $this->belongsTo(CheckItem::class);
    }
}
