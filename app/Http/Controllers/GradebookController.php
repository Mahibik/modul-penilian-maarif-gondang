<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\Nilai;
use App\Models\SiswaKelas;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    public function __construct(private GradeCalculator $calc) {}

    /** Daftar kelas yang boleh diakses (edit = kelas sendiri, lihat = sesuai matriks jenjang) */
    public function kelasIndex()
    {
        $u = auth()->user();
        $kelas = Kelas::dapatDilihat($u)->with('school')->get()
            ->sortBy(fn ($k) => [\App\Models\School::RANK[$k->school->jenjang], $k->tingkat])->groupBy('school.nama');

        return view('gradebook.kelas-index', ['kelasPerSekolah' => $kelas, 'user' => $u]);
    }

    public function kelasShow(Kelas $kelas)
    {
        $kelas->load('school');
        $this->authorize('view', $kelas);

        $ta = $kelas->school->tahunAjaranAktif();
        $mapel = KelasMapel::with('mataPelajaran')->where('kelas_id', $kelas->id)
            ->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))->get()
            ->sortBy(fn ($m) => $m->mataPelajaran->nama)->values();

        return view('gradebook.kelas-show', ['kelas' => $kelas, 'mapel' => $mapel, 'ta' => $ta, 'user' => auth()->user()]);
    }

    public function show(KelasMapel $kelasMapel)
    {
        $kelasMapel->load('kelas.school');
        $this->authorize('view', $kelasMapel);

        return view('gradebook.show', $this->data($kelasMapel) + [
            'bisaEdit' => auth()->user()->can('update', $kelasMapel),
            'predikat' => config('penilaian.predikat'),
        ]);
    }

    public function cetak(KelasMapel $kelasMapel)
    {
        $kelasMapel->load('kelas.school');
        $this->authorize('view', $kelasMapel);

        return view('gradebook.cetak', $this->data($kelasMapel));
    }

    public function store(Request $request, KelasMapel $kelasMapel)
    {
        $kelasMapel->load('kelas.school');
        $this->authorize('update', $kelasMapel);

        $request->validate([
            'nilai' => 'array',
            'nilai.*' => 'array',
            'nilai.*.*' => 'nullable|numeric|between:0,100',
        ], [
            'nilai.*.*.numeric' => 'Nilai harus berupa angka.',
            'nilai.*.*.between' => 'Nilai harus berada di antara 0 dan 100.',
        ]);

        $komponen = $kelasMapel->komponen()->where('aktif', true)->get();
        if (abs($komponen->sum('bobot') - 100) > 0.01) {
            return back()->withErrors(['bobot' => 'Total bobot komponen belum 100%. Hubungi Admin Sekolah untuk melengkapinya.']);
        }

        // Whitelist ID: cegah manipulasi ID siswa/komponen dari luar kelas ini (IDOR)
        $komponenIds = $komponen->pluck('id')->all();
        $skIds = SiswaKelas::where('kelas_id', $kelasMapel->kelas_id)
            ->where('tahun_ajaran_id', $kelasMapel->tahun_ajaran_id)->pluck('id')->all();

        $ada = Nilai::whereIn('komponen_nilai_id', $komponenIds)->whereIn('siswa_kelas_id', $skIds)
            ->get(['komponen_nilai_id', 'siswa_kelas_id'])
            ->mapWithKeys(fn ($n) => [$n->siswa_kelas_id . ':' . $n->komponen_nilai_id => true]);

        $upsert = [];
        $hapus = [];
        $now = now();

        foreach ($request->input('nilai', []) as $skId => $perKomponen) {
            abort_unless(in_array((int) $skId, $skIds, true), 403);
            foreach ($perKomponen as $kId => $v) {
                abort_unless(in_array((int) $kId, $komponenIds, true), 403);
                if ($v === null || $v === '') {
                    if ($ada->has("$skId:$kId")) {
                        $hapus[] = [(int) $skId, (int) $kId];
                    }
                    continue;
                }
                $upsert[] = ['komponen_nilai_id' => (int) $kId, 'siswa_kelas_id' => (int) $skId,
                    'nilai' => round((float) $v, 2), 'created_at' => $now, 'updated_at' => $now];
            }
        }

        DB::transaction(function () use ($upsert, $hapus, $kelasMapel) {
            foreach (array_chunk($upsert, 500) as $chunk) {
                Nilai::upsert($chunk, ['komponen_nilai_id', 'siswa_kelas_id'], ['nilai', 'updated_at']);
            }
            foreach ($hapus as [$sk, $k]) {
                Nilai::where('siswa_kelas_id', $sk)->where('komponen_nilai_id', $k)->delete();
            }
            $this->calc->rekap($kelasMapel, auth()->id());
        });

        return back()->with('sukses', 'Nilai berhasil disimpan dan rekap nilai akhir diperbarui.');
    }

    /** Data gradebook — total 4 query konstan berapa pun jumlah siswa (tanpa N+1). */
    private function data(KelasMapel $km): array
    {
        $km->load(['mataPelajaran', 'tahunAjaran', 'komponen' => fn ($q) => $q->where('aktif', true)->orderBy('urutan')]);

        $siswaKelas = SiswaKelas::with('siswa')->where('kelas_id', $km->kelas_id)
            ->where('tahun_ajaran_id', $km->tahun_ajaran_id)->get()
            ->sortBy(fn ($sk) => $sk->siswa->nama)->values();

        $nilai = Nilai::whereIn('komponen_nilai_id', $km->komponen->pluck('id'))
            ->whereIn('siswa_kelas_id', $siswaKelas->pluck('id'))->get()
            ->groupBy('siswa_kelas_id')->map(fn ($g) => $g->pluck('nilai', 'komponen_nilai_id'));

        $hasil = $siswaKelas->mapWithKeys(fn ($sk) => [$sk->id => $this->calc->hitung($km->komponen, $nilai->get($sk->id, collect()))]);

        return [
            'km' => $km, 'siswaKelas' => $siswaKelas, 'nilai' => $nilai, 'hasil' => $hasil,
            'totalBobot' => (float) $km->komponen->sum('bobot'),
        ];
    }
}
