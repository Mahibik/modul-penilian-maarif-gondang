<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Models\School;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\Yayasan;
use App\Services\GradeCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Kelompok mapel per jenjang. Sesuaikan dengan dokumen kurikulum yayasan.
     * "Muatan Lokal" otomatis tampil sebagai "Bahasa Jawa" (lihat model MataPelajaran).
     */
    private array $mapel = [
        'SD' => [ // 7 kelompok
            'PAI & Bahasa Arab' => ['Pendidikan Agama Islam & Budi Pekerti', 'Bahasa Arab'],
            'Pendidikan Pancasila' => ['Pendidikan Pancasila'],
            'Bahasa Indonesia' => ['Bahasa Indonesia'],
            'Matematika' => ['Matematika'],
            'IPAS' => ['IPAS'],
            'PJOK & Seni' => ['PJOK', 'Seni dan Budaya'],
            'Bahasa Jawa' => ['Muatan Lokal'],
        ],
        'SMP' => [ // 11 kelompok
            'PAI' => ['Pendidikan Agama Islam & Budi Pekerti'],
            'Pendidikan Pancasila' => ['Pendidikan Pancasila'],
            'Bahasa Indonesia' => ['Bahasa Indonesia'],
            'Matematika' => ['Matematika'],
            'IPA' => ['IPA'],
            'IPS' => ['IPS'],
            'Bahasa Inggris' => ['Bahasa Inggris'],
            'PJOK' => ['PJOK'],
            'Informatika' => ['Informatika'],
            'Seni dan Prakarya' => ['Seni dan Prakarya'],
            'Bahasa Jawa' => ['Muatan Lokal'],
        ],
        'SMA' => [ // 11 kelompok
            'Rumpun PAI Wajib' => ['Al-Qur\'an Hadis', 'Akidah Akhlak', 'Fikih', 'Sejarah Kebudayaan Islam'],
            'Pendidikan Pancasila' => ['Pendidikan Pancasila'],
            'Bahasa Indonesia' => ['Bahasa Indonesia'],
            'Matematika' => ['Matematika'],
            'Bahasa Inggris' => ['Bahasa Inggris'],
            'PJOK' => ['PJOK'],
            'Sejarah' => ['Sejarah'],
            'Seni Budaya' => ['Seni Budaya'],
            'Informatika' => ['Informatika'],
            'Mapel Pilihan' => ['Fisika', 'Kimia', 'Biologi', 'Ekonomi', 'Sosiologi'], // 4-5 mapel
            'Bahasa Jawa' => ['Muatan Lokal'],
        ],
    ];

    public function run(): void
    {
        $hash = Hash::make('password'); // satu kali hash, dipakai semua akun demo

        DB::transaction(function () use ($hash) {
            $yayasan = Yayasan::create(['nama' => "Yayasan Ma'arif Gondang", 'alamat' => 'Gondang']);

            User::create(['name' => 'Superadmin Yayasan', 'username' => 'superadmin', 'password' => $hash, 'role' => 'superadmin']);

            $sekolah = [
                'SD'  => ['nama' => "SD Ma'arif Gondang",  'tingkat' => range(1, 6)],
                'SMP' => ['nama' => "SMP Ma'arif Gondang", 'tingkat' => range(7, 9)],
                'SMA' => ['nama' => "SMA Ma'arif Gondang", 'tingkat' => range(10, 12)],
            ];

            foreach ($this->mapel as $jenjang => $kelompok) {
                foreach ($kelompok as $nama => $daftar) {
                    foreach ($daftar as $m) {
                        MataPelajaran::create(['jenjang' => $jenjang, 'kelompok' => $nama, 'nama' => $m, 'is_pilihan' => $nama === 'Mapel Pilihan']);
                    }
                }
            }

            $nis = 1000;
            $namaDepan = ['Ahmad', 'Siti', 'Muhammad', 'Aisyah', 'Fatimah', 'Abdullah', 'Zainab', 'Yusuf', 'Khadijah', 'Ibrahim'];
            $namaBelakang = ['Rahman', 'Hidayat', 'Maulana', 'Salsabila', 'Pratama', 'Azzahra', 'Ramadhan', 'Nurhaliza'];

            foreach ($sekolah as $jenjang => $cfg) {
                $s = School::create(['yayasan_id' => $yayasan->id, 'nama' => $cfg['nama'], 'jenjang' => $jenjang]);
                $ta = TahunAjaran::create(['school_id' => $s->id, 'nama' => '2026/2027', 'semester' => 'Ganjil', 'aktif' => true]);

                User::create(['name' => "Admin $jenjang", 'username' => 'admin_' . strtolower($jenjang), 'password' => $hash, 'role' => 'admin_sekolah', 'school_id' => $s->id]);

                $mapelIds = MataPelajaran::where('jenjang', $jenjang)->pluck('id');

                foreach ($cfg['tingkat'] as $tingkat) {
                    $kelas = Kelas::create(['school_id' => $s->id, 'tingkat' => $tingkat, 'nama' => "Kelas $tingkat"]);

                    // 1 akun wali kelas per kelas (PRD 3.A)
                    User::create(['name' => "Wali Kelas $tingkat", 'username' => 'guru_' . strtolower($jenjang) . "_$tingkat",
                        'password' => $hash, 'role' => 'guru', 'school_id' => $s->id, 'kelas_id' => $kelas->id]);

                    // siswa demo
                    $skIds = [];
                    for ($i = 0; $i < 5; $i++) {
                        $siswa = Siswa::create(['school_id' => $s->id, 'nis' => (string) ++$nis,
                            'nama' => $namaDepan[array_rand($namaDepan)] . ' ' . $namaBelakang[array_rand($namaBelakang)]]);
                        User::create(['name' => $siswa->nama, 'username' => 'siswa' . $siswa->nis, 'password' => $hash,
                            'role' => 'siswa', 'school_id' => $s->id, 'siswa_id' => $siswa->id]);
                        $skIds[] = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $kelas->id, 'tahun_ajaran_id' => $ta->id])->id;
                    }

                    // mapel + komponen + nilai contoh
                    foreach ($mapelIds as $mid) {
                        $km = KelasMapel::create(['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mid, 'tahun_ajaran_id' => $ta->id, 'kkm' => config('penilaian.kkm_default')]);
                        $komp = [];
                        foreach (config('penilaian.komponen_default') as $i => [$nm, $jn, $kt, $b]) {
                            $komp[] = KomponenNilai::create(['kelas_mapel_id' => $km->id, 'nama' => $nm, 'jenis' => $jn, 'kategori' => $kt, 'bobot' => $b, 'aktif' => true, 'urutan' => $i]);
                        }
                        $rows = [];
                        foreach ($skIds as $sk) {
                            foreach ($komp as $k) {
                                $rows[] = ['komponen_nilai_id' => $k->id, 'siswa_kelas_id' => $sk, 'nilai' => random_int(66, 98), 'created_at' => now(), 'updated_at' => now()];
                            }
                        }
                        DB::table('nilai')->insert($rows);
                        app(GradeCalculator::class)->rekap($km);
                    }
                }
            }
        });
    }
}
