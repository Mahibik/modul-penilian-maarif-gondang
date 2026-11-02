<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai';
    protected $fillable = ['komponen_nilai_id', 'siswa_kelas_id', 'nilai'];
    protected $casts = ['nilai' => 'float'];

    public function komponen() { return $this->belongsTo(KomponenNilai::class, 'komponen_nilai_id'); }
    public function siswaKelas() { return $this->belongsTo(SiswaKelas::class); }
}
