<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Services\SekolahSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    private const ATRIBUT = ['kelompok' => 'Kelompok', 'nama' => 'Nama mata pelajaran'];

    public function index()
    {
        $sekolah = auth()->user()->school;
        $ta = $sekolah->tahunAjaranAktif();

        $mapel = MataPelajaran::where('jenjang', $sekolah->jenjang)
            ->withCount(['kelasMapel as kelas_count' => fn ($q) => $q->where('tahun_ajaran_id', $ta?->id)])
            ->orderBy('kelompok')->orderBy('nama')->get()->groupBy('kelompok');

        return view('admin.mapel-index', ['mapel' => $mapel, 'sekolah' => $sekolah]);
    }

    public function create()
    {
        return view('admin.mapel-form', ['mapel' => null] + $this->dataForm());
    }

    public function store(Request $request, SekolahSetup $setup)
    {
        $sekolah = auth()->user()->school;
        $data = $request->validate($this->aturan(), [], self::ATRIBUT);

        DB::transaction(function () use ($data, $sekolah, $setup, $request) {
            $mapel = MataPelajaran::create([
                'jenjang' => $sekolah->jenjang, 'kelompok' => $data['kelompok'], 'nama' => $data['nama'],
                'is_pilihan' => $request->boolean('is_pilihan'),
            ]);
            if ($ta = $sekolah->tahunAjaranAktif()) {
                $setup->pasangkanMapel($sekolah, $ta, $mapel); // semua kelas + komponen awal
            }
        });

        return redirect()->route('adm.mapel.index')->with('sukses', 'Mata pelajaran ditambahkan ke semua kelas dengan komponen awal. Atur bobotnya di menu Komponen & bobot.');
    }

    public function edit(MataPelajaran $mapel)
    {
        $this->milik($mapel);

        return view('admin.mapel-form', ['mapel' => $mapel] + $this->dataForm());
    }

    public function update(Request $request, MataPelajaran $mapel)
    {
        $this->milik($mapel);
        $data = $request->validate($this->aturan(), [], self::ATRIBUT);
        $mapel->update($data + ['is_pilihan' => $request->boolean('is_pilihan')]);

        return redirect()->route('adm.mapel.index')->with('sukses', 'Mata pelajaran disimpan.');
    }

    private function milik(MataPelajaran $m): void
    {
        abort_unless($m->jenjang === auth()->user()->school->jenjang, 403);
    }

    private function aturan(): array
    {
        return ['kelompok' => 'required|string|max:120', 'nama' => 'required|string|max:150'];
    }

    private function dataForm(): array
    {
        $sekolah = auth()->user()->school;

        return ['sekolah' => $sekolah, 'kelompokAda' => MataPelajaran::where('jenjang', $sekolah->jenjang)->distinct()->orderBy('kelompok')->pluck('kelompok')];
    }
}
