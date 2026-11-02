<?php

namespace App\Console\Commands;

use App\Models\KelasMapel;
use App\Models\KomponenNilai;
use App\Services\GradeCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Menambah komponen Keterampilan (Praktik) ke mapel yang belum punya,
 * HANYA untuk mapel yang masih memakai komponen bawaan lama (tidak pernah diubah Admin).
 */
class TambahKeterampilan extends Command
{
    protected $signature = 'penilaian:tambah-keterampilan {--dry-run : Hanya tampilkan, tanpa mengubah data}';

    protected $description = 'Tambah komponen Keterampilan ke mapel yang masih memakai komponen bawaan lama';

    private const LAMA = [['Tugas', 20], ['Ulangan Harian', 20], ['PTS', 20], ['PAS/SAS', 30], ['Sikap', 10]];

    public function handle(GradeCalculator $calc): int
    {
        $baru = config('penilaian.komponen_default');
        $dry = $this->option('dry-run');
        $diubah = $sudah = $dilewati = 0;

        KelasMapel::with('komponen')->chunkById(100, function ($kms) use ($baru, $dry, $calc, &$diubah, &$sudah, &$dilewati) {
            foreach ($kms as $km) {
                if ($km->komponen->contains(fn ($k) => $k->aktif && $k->kategori === 'keterampilan')) {
                    $sudah++;
                    continue;
                }

                $cocok = $km->komponen->count() === count(self::LAMA)
                    && collect(self::LAMA)->every(function ($l, $i) use ($km) {
                        $k = $km->komponen->values()->get($i);

                        return $k && $k->aktif && $k->nama === $l[0] && (float) $k->bobot === (float) $l[1];
                    });

                if (! $cocok) {
                    $dilewati++; // sudah diatur manual oleh Admin
                    continue;
                }

                $diubah++;
                if ($dry) {
                    continue;
                }

                DB::transaction(function () use ($km, $baru, $calc) {
                    foreach ($baru as $i => [$nama, $jenis, $kategori, $bobot]) {
                        $ada = $km->komponen->firstWhere('nama', $nama);
                        if ($ada) {
                            $ada->update(['bobot' => $bobot, 'urutan' => $i]);
                        } else {
                            KomponenNilai::create(['kelas_mapel_id' => $km->id, 'nama' => $nama, 'jenis' => $jenis,
                                'kategori' => $kategori, 'bobot' => $bobot, 'aktif' => true, 'urutan' => $i]);
                        }
                    }
                    $calc->rekap($km);
                });
            }
        });

        $this->info(($dry ? '[simulasi] ' : '') . "Diperbarui: $diubah mapel · Sudah punya keterampilan: $sudah · Dilewati (diatur manual Admin): $dilewati");

        return self::SUCCESS;
    }
}
