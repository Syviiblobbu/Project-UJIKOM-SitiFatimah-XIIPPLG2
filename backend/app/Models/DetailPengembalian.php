<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengembalian extends Model
{
    protected $table = 'detail_pengembalian';

    protected $fillable = [
        'pengembalian_id',
        'detail_pinjam_id',
        'kondisi_kembali',
        'denda_kerusakan',
    ];

    protected $casts = [
        'denda_kerusakan' => 'integer',
    ];

    public function pengembalian()
    {
        return $this->belongsTo(Pengembalian::class);
    }

    public function detailPinjam()
    {
        return $this->belongsTo(DetailPinjam::class);
    }
}