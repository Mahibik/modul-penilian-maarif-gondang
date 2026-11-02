<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    public const RANK = ['SD' => 1, 'SMP' => 2, 'SMA' => 3];

    protected $table = 'schools';
    protected $fillable = ['yayasan_id', 'nama', 'jenjang', 'npsn'];

    public function yayasan() { return $this->belongsTo(Yayasan::class); }
    public function kelas() { return $this->hasMany(Kelas::class); }
    public function siswa() { return $this->hasMany(Siswa::class); }
    public function tahunAjaran() { return $this->hasMany(TahunAjaran::class); }

    public function tahunAjaranAktif(): ?TahunAjaran
    {
        return $this->tahunAjaran()->where('aktif', true)->first();
    }

    public static function jenjangDapatDilihat(string $jenjang): array
    {
        return array_keys(array_filter(self::RANK, fn ($r) => $r <= self::RANK[$jenjang]));
    }
}
