<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\RekapNilai;
use App\Models\School;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Services\GradeCalculator;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(GradeCalculator $calc)
    {
        $u = auth()->user();

        return match ($u->role) {
            'superadmin'    => $this->superadmin(),
            'admin_sekolah' => $this->admin($u),
            'guru'          => $this->guru($u),
            default         => $this->siswa($u, $calc),
        };
    }

    private function superadmin()
    {
        $rata = DB::table('rekap_nilai')
            ->join('kelas_mapel', 'kelas_mapel.id', '=', 'rekap_nilai.kelas_mapel_id')
            ->join('kelas', 'kelas.id', '=', 'kelas_mapel.kelas_id')
            ->groupBy('kelas.school_id')
            ->selectRaw('kelas.school_id, AVG(rekap_nilai.nilai_akhir) as rata')
            ->pluck('rata', 'school_id');

        $sekolah = School::withCount(['kelas', 'siswa'])->orderBy('id')->get()
            ->each(fn ($s) => $s->rata = isset($rata[$s->id]) ? round($rata[$s->id], 1) : null);

        return view('dashboard.superadmin', [
            'sekolah' => $sekolah,
            'totalSiswa' => $sekolah->sum('siswa_count'),
            'totalKelas' => $sekolah->sum('kelas_count'),
        ]);
    }

    private function admin($u)
    {
        $sekolah = $u->school;
        $ta = $sekolah->tahunAjaranAktif();

        $mapel = KelasMapel::whereHas('kelas', fn ($q) => $q->where('school_id', $sekolah->id))
            ->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->withSum(['komponen as total_bobot' => fn ($q) => $q->where('aktif', true)], 'bobot')
            ->get();

        return view('dashboard.admin', [
            'sekolah' => $sekolah, 'ta' => $ta,
            'jumlahSiswa' => Siswa::where('school_id', $sekolah->id)->count(),
            'jumlahKelas' => Kelas::where('school_id', $sekolah->id)->count(),
            'jumlahMapel' => MataPelajaran::where('jenjang', $sekolah->jenjang)->count(),
            'bobotBelumSiap' => $mapel->filter(fn ($m) => abs($m->total_bobot - 100) > 0.01)->count(),
        ]);
    }

    private function guru($u)
    {
        $kelas = $u->kelas()->with('school')->first();
        $ta = $u->school->tahunAjaranAktif();

        $mapel = KelasMapel::with('mataPelajaran')
            ->where('kelas_id', $kelas->id)->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->withCount('rekap as siswa_count')
            ->withCount(['rekap as belum_lengkap' => fn ($q) => $q->where('lengkap', false)])
            ->get();

        return view('dashboard.guru', [
            'kelas' => $kelas, 'ta' => $ta, 'mapel' => $mapel,
            'jumlahSiswa' => SiswaKelas::where('kelas_id', $kelas->id)->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))->count(),
            'rataKelas' => round((float) RekapNilai::whereIn('kelas_mapel_id', $mapel->pluck('id'))->avg('nilai_akhir'), 1),
        ]);
    }

    private function siswa($u, GradeCalculator $calc)
    {
        $siswa = $u->siswa;
        abort_unless($siswa, 403);

        $sk = SiswaKelas::with(['kelas', 'tahunAjaran'])
            ->where('siswa_id', $siswa->id)->whereHas('tahunAjaran', fn ($q) => $q->where('aktif', true))->first();
        abort_unless($sk, 404, 'Data kelas siswa pada tahun ajaran aktif belum tersedia.');

        $kms = KelasMapel::with(['mataPelajaran', 'komponen' => fn ($q) => $q->where('aktif', true)])
            ->where('kelas_id', $sk->kelas_id)->where('tahun_ajaran_id', $sk->tahun_ajaran_id)->get();

        $nilai = Nilai::where('siswa_kelas_id', $sk->id)->pluck('nilai', 'komponen_nilai_id');
        $rekap = RekapNilai::where('siswa_kelas_id', $sk->id)->get()->keyBy('kelas_mapel_id');

        $baris = $kms->map(function ($km) use ($calc, $nilai, $rekap) {
            $r = $rekap->get($km->id);

            return [
                'mapel' => $km->mataPelajaran->nama,
                'kkm' => $km->kkm,
                'kategori' => $calc->perKategori($km->komponen, $nilai),
                'akhir' => $r?->nilai_akhir,
                'predikat' => $r?->predikat,
                'lengkap' => $r?->lengkap ?? false,
            ];
        })->sortBy('mapel')->values();

        return view('dashboard.siswa', [
            'siswa' => $siswa, 'sk' => $sk, 'baris' => $baris,
            'rata' => round((float) $baris->whereNotNull('akhir')->avg('akhir'), 1),
            'tuntas' => $baris->filter(fn ($b) => $b['akhir'] !== null && $b['akhir'] >= $b['kkm'])->count(),
        ]);
    }
}
