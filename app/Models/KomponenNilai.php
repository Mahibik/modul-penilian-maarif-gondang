<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenNilai extends Model
{
    protected $table = 'komponen_nilai';
    protected $fillable = ['kelas_mapel_id', 'nama', 'jenis', 'kategori', 'bobot', 'aktif', 'urutan'];
    protected $casts = ['aktif' => 'boolean', 'bobot' => 'float'];

    public function kelasMapel() { return $this->belongsTo(KelasMapel::class); }
    public function nilai() { return $this->hasMany(Nilai::class); }
}
