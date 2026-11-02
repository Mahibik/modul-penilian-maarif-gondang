<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $fillable = ['jenjang', 'kelompok', 'nama', 'is_pilihan'];
    protected $casts = ['is_pilihan' => 'boolean'];

    public function kelasMapel() { return $this->hasMany(KelasMapel::class, 'mata_pelajaran_id'); }

    /** Aturan PRD 3.C: "Muatan Lokal" selalu tampil sebagai "Bahasa Jawa". */
    protected function nama(): Attribute
    {
        $ganti = fn ($v) => is_string($v) && strcasecmp(trim($v), 'Muatan Lokal') === 0 ? 'Bahasa Jawa' : $v;

        return Attribute::make(get: $ganti, set: $ganti);
    }

    protected function kelompok(): Attribute
    {
        $ganti = fn ($v) => is_string($v) && strcasecmp(trim($v), 'Muatan Lokal') === 0 ? 'Bahasa Jawa' : $v;

        return Attribute::make(get: $ganti, set: $ganti);
    }
}
