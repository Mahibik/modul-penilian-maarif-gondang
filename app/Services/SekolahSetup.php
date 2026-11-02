<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Models\School;
use App\Models\TahunAjaran;

/** Menyiapkan kelas, mapel per kelas, dan komponen awal. */
class SekolahSetup
{
    public const TINGKAT = ['SD' => [1, 6], 'SMP' => [7, 9], 'SMA' => [10, 12]];

    /** Sekolah baru: semua kelas sesuai jenjang + semua mapel jenjang. */
    public function siapkan(School $s, TahunAjaran $ta): void
    {
        [$awal, $akhir] = self::TINGKAT[$s->jenjang];

        foreach (range($awal, $akhir) as $tingkat) {
            $kelas = Kelas::firstOrCreate(['school_id' => $s->id, 'tingkat' => $tingkat], ['nama' => "Kelas $tingkat"]);
            $this->siapkanKelas($s, $ta, $kelas);
        }
    }

    /** Kelas baru: pasangkan semua mapel jenjang. */
    public function siapkanKelas(School $s, TahunAjaran $ta, Kelas $kelas): void
    {
        foreach (MataPelajaran::where('jenjang', $s->jenjang)->pluck('id') as $mid) {
            $this->pasang($kelas, $mid, $ta);
        }
    }

    /** Mapel baru: pasangkan ke semua kelas di sekolah. */
    public function pasangkanMapel(School $s, TahunAjaran $ta, MataPelajaran $m): void
    {
        foreach (Kelas::where('school_id', $s->id)->get() as $kelas) {
            $this->pasang($kelas, $m->id, $ta);
        }
    }

    private function pasang(Kelas $kelas, int $mapelId, TahunAjaran $ta): void
    {
        $km = KelasMapel::firstOrCreate(
            ['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapelId, 'tahun_ajaran_id' => $ta->id],
            ['kkm' => config('penilaian.kkm_default')]
        );

        if ($km->wasRecentlyCreated) {
            $now = now();
            KomponenNilai::insert(collect(config('penilaian.komponen_default'))->map(fn ($k, $i) => [
                'kelas_mapel_id' => $km->id, 'nama' => $k[0], 'jenis' => $k[1], 'kategori' => $k[2],
                'bobot' => $k[3], 'aktif' => true, 'urutan' => $i, 'created_at' => $now, 'updated_at' => $now,
            ])->all());
        }
    }
}
