<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    // Nama tabel (opsional, karena default-nya plural dari model "parts")
    protected $table = 'spareparts';

    // Primary key (default: id)
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'harga',
        'stok',
        'uom',
    ];
}
