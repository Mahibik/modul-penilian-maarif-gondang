<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\TahunAjaran;
use App\Models\Yayasan;
use App\Services\SekolahSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SekolahController extends Controller
{
    private const ATURAN_TA = [
        'ta_nama' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
        'semester' => 'required|in:Ganjil,Genap',
    ];

    private const PESAN = [
        'ta_nama.regex' => 'Tahun ajaran harus berformat 2026/2027.',
    ];

    public function index()
    {
        $sekolah = School::withCount(['kelas', 'siswa'])
            ->with(['tahunAjaran' => fn ($q) => $q->where('aktif', true)])->orderBy('id')->get();

        return view('kelola.sekolah-index', ['sekolah' => $sekolah]);
    }

    public function create()
    {
        return view('kelola.sekolah-form', ['sekolah' => null, 'ta' => null]);
    }

    public function store(Request $request, SekolahSetup $setup)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'jenjang' => 'required|in:SD,SMP,SMA',
            'npsn' => 'nullable|string|max:20',
        ] + self::ATURAN_TA, self::PESAN);

        DB::transaction(function () use ($data, $setup) {
            $sekolah = School::create([
                'yayasan_id' => Yayasan::firstOrFail()->id,
                'nama' => $data['nama'], 'jenjang' => $data['jenjang'], 'npsn' => $data['npsn'] ?? null,
            ]);
            $ta = TahunAjaran::create(['school_id' => $sekolah->id, 'nama' => $data['ta_nama'], 'semester' => $data['semester'], 'aktif' => true]);
            $setup->siapkan($sekolah, $ta); // kelas + mapel + komponen awal
        });

        return redirect()->route('sa.sekolah.index')
            ->with('sukses', 'Sekolah ditambahkan lengkap dengan kelas, mata pelajaran, dan komponen awal.');
    }

    public function edit(School $school)
    {
        return view('kelola.sekolah-form', ['sekolah' => $school, 'ta' => $school->tahunAjaranAktif()]);
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'npsn' => 'nullable|string|max:20',
        ] + self::ATURAN_TA, self::PESAN);

        DB::transaction(function () use ($school, $data) {
            $school->update(['nama' => $data['nama'], 'npsn' => $data['npsn'] ?? null]);
            $ta = $school->tahunAjaranAktif() ?? new TahunAjaran(['school_id' => $school->id, 'aktif' => true]);
            $ta->fill(['nama' => $data['ta_nama'], 'semester' => $data['semester']])->save();
        });

        return redirect()->route('sa.sekolah.index')->with('sukses', 'Data sekolah disimpan.');
    }
}
