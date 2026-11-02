<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasMapel;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KomponenController extends Controller
{
    public function index()
    {
        $sekolah = auth()->user()->school;
        $ta = $sekolah->tahunAjaranAktif();

        $daftar = KelasMapel::with(['kelas', 'mataPelajaran'])
            ->whereHas('kelas', fn ($q) => $q->where('school_id', $sekolah->id))
            ->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->withSum(['komponen as total_bobot' => fn ($q) => $q->where('aktif', true)], 'bobot')
            ->withCount('komponen')
            ->get()
            ->sortBy(fn ($m) => [$m->kelas->tingkat, $m->mataPelajaran->nama])
            ->groupBy(fn ($m) => $m->kelas->nama);

        return view('admin.komponen-index', ['daftar' => $daftar, 'sekolah' => $sekolah]);
    }

    public function edit(KelasMapel $kelasMapel)
    {
        $kelasMapel->load(['kelas', 'mataPelajaran', 'komponen']);
        $this->authorize('manageKomponen', $kelasMapel);

        return view('admin.komponen-edit', [
            'km' => $kelasMapel,
            'jenis' => config('penilaian.jenis_komponen'),
            'kategori' => config('penilaian.kategori'),
        ]);
    }

    public function update(Request $request, KelasMapel $kelasMapel, GradeCalculator $calc)
    {
        $kelasMapel->load('kelas');
        $this->authorize('manageKomponen', $kelasMapel);

        $data = $request->validate([
            'kkm' => 'required|integer|between:0,100',
            'komponen' => 'required|array|min:1',
            'komponen.*.id' => 'nullable|integer',
            'komponen.*.nama' => 'required|string|max:100',
            'komponen.*.jenis' => ['required', Rule::in(config('penilaian.jenis_komponen'))],
            'komponen.*.kategori' => ['required', Rule::in(array_keys(config('penilaian.kategori')))],
            'komponen.*.bobot' => 'required|numeric|between:0,100',
            'komponen.*.aktif' => 'nullable|boolean',
        ]);

        $total = collect($data['komponen'])->filter(fn ($k) => ! empty($k['aktif']))->sum('bobot');
        if (abs($total - 100) > 0.01) {
            return back()->withInput()->withErrors(['bobot' => "Total bobot komponen aktif adalah {$total}%. Harus tepat 100%."]);
        }

        $milikSendiri = $kelasMapel->komponen()->pluck('id')->all();

        DB::transaction(function () use ($data, $kelasMapel, $milikSendiri, $calc) {
            $kelasMapel->update(['kkm' => $data['kkm']]);
            $dipakai = [];

            foreach (array_values($data['komponen']) as $i => $k) {
                $atribut = ['nama' => $k['nama'], 'jenis' => $k['jenis'], 'kategori' => $k['kategori'],
                    'bobot' => $k['bobot'], 'aktif' => ! empty($k['aktif']), 'urutan' => $i];

                // hanya ID milik kelas_mapel ini yang boleh di-update (anti-IDOR)
                if (! empty($k['id']) && in_array((int) $k['id'], $milikSendiri, true)) {
                    $kelasMapel->komponen()->whereKey($k['id'])->update($atribut);
                    $dipakai[] = (int) $k['id'];
                } else {
                    $dipakai[] = $kelasMapel->komponen()->create($atribut)->id;
                }
            }

            $kelasMapel->komponen()->whereNotIn('id', $dipakai)->delete();
            $calc->rekap($kelasMapel, auth()->id());
        });

        return redirect()->route('komponen.index')->with('sukses', 'Komponen dan bobot penilaian disimpan.');
    }
}
