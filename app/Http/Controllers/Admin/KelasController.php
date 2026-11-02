<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Services\SekolahSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index()
    {
        $sekolah = auth()->user()->school;
        $ta = $sekolah->tahunAjaranAktif();

        $kelas = Kelas::where('school_id', $sekolah->id)
            ->withCount([
                'siswaKelas as siswa_count' => fn ($q) => $q->where('tahun_ajaran_id', $ta?->id),
                'kelasMapel as mapel_count' => fn ($q) => $q->where('tahun_ajaran_id', $ta?->id),
            ])->orderBy('tingkat')->get();

        $wali = User::where('role', 'guru')->where('school_id', $sekolah->id)->pluck('name', 'kelas_id');

        return view('admin.kelas-index', ['kelas' => $kelas, 'wali' => $wali, 'sekolah' => $sekolah, 'sisa' => $this->tingkatTersisa($sekolah)]);
    }

    public function create()
    {
        $sekolah = auth()->user()->school;

        return view('admin.kelas-form', ['kelas' => null, 'sisa' => $this->tingkatTersisa($sekolah), 'sekolah' => $sekolah]);
    }

    public function store(Request $request, SekolahSetup $setup)
    {
        $sekolah = auth()->user()->school;
        $sisa = $this->tingkatTersisa($sekolah);

        $data = $request->validate([
            'tingkat' => ['required', 'integer', Rule::in($sisa)],
            'nama' => 'required|string|max:50',
        ], ['tingkat.in' => 'Tingkat ini tidak tersedia atau sudah ada.'], ['tingkat' => 'Tingkat', 'nama' => 'Nama kelas']);

        DB::transaction(function () use ($data, $sekolah, $setup) {
            $kelas = Kelas::create(['school_id' => $sekolah->id, 'tingkat' => $data['tingkat'], 'nama' => $data['nama']]);
            if ($ta = $sekolah->tahunAjaranAktif()) {
                $setup->siapkanKelas($sekolah, $ta, $kelas); // mapel + komponen awal
            }
        });

        return redirect()->route('adm.kelas.index')->with('sukses', 'Kelas ditambahkan lengkap dengan mata pelajaran dan komponen awal.');
    }

    public function edit(Kelas $kelas)
    {
        $this->milik($kelas);

        return view('admin.kelas-form', ['kelas' => $kelas, 'sisa' => [], 'sekolah' => auth()->user()->school]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $this->milik($kelas);
        $data = $request->validate(['nama' => 'required|string|max:50'], [], ['nama' => 'Nama kelas']);
        $kelas->update($data);

        return redirect()->route('adm.kelas.index')->with('sukses', 'Nama kelas disimpan.');
    }

    private function milik(Kelas $k): void
    {
        abort_unless($k->school_id === auth()->user()->school_id, 403);
    }

    /** Tingkat sesuai jenjang yang belum punya kelas. */
    private function tingkatTersisa($sekolah): array
    {
        [$awal, $akhir] = SekolahSetup::TINGKAT[$sekolah->jenjang];
        $ada = Kelas::where('school_id', $sekolah->id)->pluck('tingkat')->all();

        return array_values(array_diff(range($awal, $akhir), $ada));
    }
}
