<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapNilai extends Model
{
    protected $table = 'rekap_nilai';
    protected $fillable = ['kelas_mapel_id', 'siswa_kelas_id', 'nilai_akhir', 'predikat', 'lengkap', 'diubah_oleh'];
    protected $casts = ['nilai_akhir' => 'float', 'lengkap' => 'boolean'];

    public function kelasMapel() { return $this->belongsTo(KelasMapel::class); }
    public function siswaKelas() { return $this->belongsTo(SiswaKelas::class); }
}
