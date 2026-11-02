<?php

namespace App\Services;

use App\Models\KelasMapel;
use App\Models\Nilai;
use App\Models\RekapNilai;
use App\Models\SiswaKelas;
use Illuminate\Support\Collection;

class GradeCalculator
{
    public function predikat(float $nilai): string
    {
        foreach (config('penilaian.predikat') as $p) {
            if ($nilai >= $p['min']) {
                return $p['huruf'];
            }
        }

        return 'D';
    }

    /**
     * Nilai akhir = Σ (nilai komponen × bobot / 100).
     *
     * @param  Collection  $komponen  komponen aktif (punya ->id, ->bobot)
     * @param  Collection  $nilaiSiswa  [komponen_id => nilai]
     */
    public function hitung(Collection $komponen, Collection $nilaiSiswa): array
    {
        $total = 0.0;
        $lengkap = $komponen->isNotEmpty();

        foreach ($komponen as $k) {
            $v = $nilaiSiswa->get($k->id);
            if ($v === null) {
                $lengkap = false;
                $v = 0;
            }
            $total += (float) $v * (float) $k->bobot / 100;
        }

        $total = round($total, 2);

        return ['nilai_akhir' => $total, 'predikat' => $this->predikat($total), 'lengkap' => $lengkap];
    }

    /** Rata-rata tertimbang per kategori (pengetahuan / keterampilan / sikap). */
    public function perKategori(Collection $komponen, Collection $nilaiSiswa): array
    {
        return $komponen->groupBy('kategori')->map(function (Collection $grup) use ($nilaiSiswa) {
            $bobot = $grup->sum('bobot');
            if ($bobot <= 0) {
                return null;
            }
            $skor = $grup->sum(fn ($k) => (float) $nilaiSiswa->get($k->id, 0) * (float) $k->bobot);

            return round($skor / $bobot, 1);
        })->all();
    }

    /** Hitung ulang & simpan rekap satu mapel di satu kelas (3 query, tanpa N+1). */
    public function rekap(KelasMapel $km, ?int $userId = null): void
    {
        $komponen = $km->komponen()->where('aktif', true)->get();
        $skIds = SiswaKelas::where('kelas_id', $km->kelas_id)
            ->where('tahun_ajaran_id', $km->tahun_ajaran_id)->pluck('id');

        $nilai = Nilai::whereIn('komponen_nilai_id', $komponen->pluck('id'))
            ->whereIn('siswa_kelas_id', $skIds)->get()
            ->groupBy('siswa_kelas_id')
            ->map(fn ($g) => $g->pluck('nilai', 'komponen_nilai_id'));

        $now = now();
        $rows = $skIds->map(function ($id) use ($km, $komponen, $nilai, $userId, $now) {
            $h = $this->hitung($komponen, $nilai->get($id, collect()));

            return [
                'kelas_mapel_id' => $km->id,
                'siswa_kelas_id' => $id,
                'nilai_akhir'    => $h['nilai_akhir'],
                'predikat'       => $h['predikat'],
                'lengkap'        => $h['lengkap'],
                'diubah_oleh'    => $userId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        })->values()->all();

        if ($rows) {
            RekapNilai::upsert($rows, ['kelas_mapel_id', 'siswa_kelas_id'],
                ['nilai_akhir', 'predikat', 'lengkap', 'diubah_oleh', 'updated_at']);
        }
    }
}
