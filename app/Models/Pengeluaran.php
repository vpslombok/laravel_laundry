<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = [
        'user_id',
        'jumlah',
        'keterangan',
    ];

    public function transaksi()
    {
        return $this->hasMany(transaksi::class);
    }

    public function harga_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function cabang()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
