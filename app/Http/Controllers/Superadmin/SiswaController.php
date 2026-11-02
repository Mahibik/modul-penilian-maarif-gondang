<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\School;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private const ATRIBUT = ['school_id' => 'Sekolah', 'nis' => 'NIS', 'nama' => 'Nama', 'kelas_id' => 'Kelas', 'password' => 'Kata sandi'];

    public function index(Request $request)
    {
        $siswa = Siswa::with([
            'school', 'user:id,siswa_id,username,aktif',
            'siswaKelas' => fn ($q) => $q->whereHas('tahunAjaran', fn ($t) => $t->where('aktif', true))->with('kelas'),
        ])
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('nama', 'like', "%$v%")->orWhere('nis', 'like', "%$v%")))
            ->when($request->school_id, fn ($q, $v) => $q->where('school_id', $v))
            ->when($request->kelas_id, fn ($q, $v) => $q->whereHas('siswaKelas', fn ($s) => $s->where('kelas_id', $v)))
            ->orderBy('school_id')->orderBy('nama')->paginate(20)->withQueryString();

        return view('kelola.siswa-index', [
            'siswa' => $siswa, 'sekolah' => School::orderBy('id')->get(),
            'kelas' => Kelas::with('school')->orderBy('school_id')->orderBy('tingkat')->get(),
        ]);
    }

    public function create()
    {
        return view('kelola.siswa-form', ['siswa' => null, 'sk' => null, 'punyaNilai' => false] + $this->dataForm(null));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->where('school_id', $request->input('school_id'))],
            'nama' => 'required|string|max:150',
            'kelas_id' => ['required', Rule::exists('kelas', 'id')->where('school_id', $request->input('school_id'))],
            'password' => 'nullable|string|min:8',
        ], ['nis.unique' => 'NIS ini sudah terdaftar di sekolah tersebut.'], self::ATRIBUT);

        $sekolah = School::findOrFail($data['school_id']);
        $ta = $sekolah->tahunAjaranAktif();
        if (! $ta) {
            return back()->withInput()->withErrors(['school_id' => 'Sekolah ini belum punya tahun ajaran aktif. Atur dulu di menu Sekolah.']);
        }

        DB::transaction(function () use ($data, $sekolah, $ta, $request) {
            $siswa = Siswa::create(['school_id' => $sekolah->id, 'nis' => $data['nis'], 'nama' => $data['nama']]);
            SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $data['kelas_id'], 'tahun_ajaran_id' => $ta->id]);

            if ($request->boolean('buat_akun', true)) {
                $username = 'siswa' . $siswa->nis;
                if (User::where('username', $username)->exists()) {
                    $username .= '_' . strtolower($sekolah->jenjang);
                }
                User::create([
                    'name' => $siswa->nama, 'username' => $username,
                    'password' => $data['password'] ?: $siswa->nis, // kosong = NIS
                    'role' => 'siswa', 'aktif' => true, 'school_id' => $sekolah->id, 'siswa_id' => $siswa->id,
                ]);
            }
        });

        return redirect()->route('sa.siswa.index')->with('sukses', 'Siswa ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $siswa->load('user');
        $sk = $this->kelasAktif($siswa);

        return view('kelola.siswa-form', [
            'siswa' => $siswa, 'sk' => $sk,
            'punyaNilai' => $sk ? Nilai::where('siswa_kelas_id', $sk->id)->exists() : false,
        ] + $this->dataForm($sk?->kelas_id));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->where('school_id', $siswa->school_id)->ignore($siswa->id)],
            'nama' => 'required|string|max:150',
            'kelas_id' => ['nullable', Rule::exists('kelas', 'id')->where('school_id', $siswa->school_id)],
        ], ['nis.unique' => 'NIS ini sudah terdaftar di sekolah tersebut.'], self::ATRIBUT);

        $sk = $this->kelasAktif($siswa);

        if ($sk && ! empty($data['kelas_id']) && (int) $data['kelas_id'] !== $sk->kelas_id) {
            if (Nilai::where('siswa_kelas_id', $sk->id)->exists()) {
                return back()->withInput()->withErrors(['kelas_id' => 'Kelas tidak bisa diubah karena siswa sudah punya nilai.']);
            }
        }

        DB::transaction(function () use ($siswa, $sk, $data) {
            $siswa->update(['nis' => $data['nis'], 'nama' => $data['nama']]);
            User::where('siswa_id', $siswa->id)->update(['name' => $data['nama']]);
            if ($sk && ! empty($data['kelas_id'])) {
                $sk->update(['kelas_id' => $data['kelas_id']]);
            }
        });

        return redirect()->route('sa.siswa.index')->with('sukses', 'Data siswa disimpan.');
    }

    public function destroy(Siswa $siswa)
    {
        DB::transaction(function () use ($siswa) {
            User::where('siswa_id', $siswa->id)->delete();
            $siswa->delete(); // siswa_kelas, nilai, rekap ikut terhapus (cascade)
        });

        return redirect()->route('sa.siswa.index')->with('sukses', 'Siswa dan seluruh nilainya dihapus.');
    }

    private function kelasAktif(Siswa $siswa): ?SiswaKelas
    {
        return SiswaKelas::where('siswa_id', $siswa->id)
            ->whereHas('tahunAjaran', fn ($t) => $t->where('aktif', true))->first();
    }

    private function dataForm(?int $kelasDipilih): array
    {
        $kelasData = Kelas::with('school')->orderBy('school_id')->orderBy('tingkat')->get()->map(fn ($k) => [
            'id' => $k->id, 'school_id' => $k->school_id, 'label' => $k->nama . ' — ' . $k->school->jenjang, 'terpakai' => null,
        ])->values()->all();

        return ['sekolah' => School::orderBy('id')->get(), 'kelasData' => $kelasData, 'kelasDipilih' => old('kelas_id', $kelasDipilih)];
    }
}
