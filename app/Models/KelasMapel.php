<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasMapel extends Model
{
    protected $table = 'kelas_mapel';
    protected $fillable = ['kelas_id', 'mata_pelajaran_id', 'tahun_ajaran_id', 'kkm'];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function mataPelajaran() { return $this->belongsTo(MataPelajaran::class); }
    public function tahunAjaran() { return $this->belongsTo(TahunAjaran::class); }
    public function komponen() { return $this->hasMany(KomponenNilai::class)->orderBy('urutan'); }
    public function rekap() { return $this->hasMany(RekapNilai::class); }
}
