<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    /** @use HasFactory<\Database\Factories\AnggotaFactory> */
    use HasFactory;

    protected $table = 'anggotas';

    protected $fillable = [
        'name',
        'alamat',
        'telepon',
    ];

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }
}
