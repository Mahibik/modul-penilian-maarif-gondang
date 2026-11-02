<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Admin Sekolah menambah dan mengubah siswa di sekolahnya sendiri. */
class SiswaController extends Controller
{
    private const ATRIBUT = ['nis' => 'NIS', 'nama' => 'Nama', 'kelas_id' => 'Kelas', 'password' => 'Kata sandi'];

    public function index(Request $request)
    {
        $sid = auth()->user()->school_id;

        $siswa = Siswa::with([
            'user:id,siswa_id,username,aktif',
            'siswaKelas' => fn ($q) => $q->whereHas('tahunAjaran', fn ($t) => $t->where('aktif', true))->with('kelas'),
        ])->where('school_id', $sid)
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('nama', 'like', "%$v%")->orWhere('nis', 'like', "%$v%")))
            ->when($request->kelas_id, fn ($q, $v) => $q->whereHas('siswaKelas', fn ($s) => $s->where('kelas_id', $v)))
            ->orderBy('nama')->paginate(20)->withQueryString();

        return view('admin.siswa-index', ['siswa' => $siswa, 'kelas' => Kelas::where('school_id', $sid)->orderBy('tingkat')->get()]);
    }

    public function create()
    {
        return view('admin.siswa-form', ['siswa' => null, 'punyaNilai' => false] + $this->dataForm(null));
    }

    public function store(Request $request)
    {
        $sekolah = auth()->user()->school;

        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->where('school_id', $sekolah->id)],
            'nama' => 'required|string|max:150',
            'kelas_id' => ['required', Rule::exists('kelas', 'id')->where('school_id', $sekolah->id)],
            'password' => 'nullable|string|min:8',
        ], ['nis.unique' => 'NIS ini sudah terdaftar di sekolah Anda.'], self::ATRIBUT);

        $ta = $sekolah->tahunAjaranAktif();
        if (! $ta) {
            return back()->withInput()->withErrors(['kelas_id' => 'Sekolah belum punya tahun ajaran aktif. Hubungi Superadmin.']);
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
                    'name' => $siswa->nama, 'username' => $username, 'password' => $data['password'] ?: $siswa->nis,
                    'role' => 'siswa', 'aktif' => true, 'school_id' => $sekolah->id, 'siswa_id' => $siswa->id,
                ]);
            }
        });

        return redirect()->route('adm.siswa.index')->with('sukses', 'Siswa ditambahkan ke ' . Kelas::find($data['kelas_id'])->nama . '.');
    }

    public function edit(Siswa $siswa)
    {
        $this->milik($siswa);
        $siswa->load('user');
        $sk = $this->kelasAktif($siswa);

        return view('admin.siswa-form', [
            'siswa' => $siswa,
            'punyaNilai' => $sk ? Nilai::where('siswa_kelas_id', $sk->id)->exists() : false,
        ] + $this->dataForm($sk?->kelas_id));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->milik($siswa);

        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->where('school_id', $siswa->school_id)->ignore($siswa->id)],
            'nama' => 'required|string|max:150',
            'kelas_id' => ['nullable', Rule::exists('kelas', 'id')->where('school_id', $siswa->school_id)],
        ], ['nis.unique' => 'NIS ini sudah terdaftar di sekolah Anda.'], self::ATRIBUT);

        $sk = $this->kelasAktif($siswa);

        if ($sk && ! empty($data['kelas_id']) && (int) $data['kelas_id'] !== $sk->kelas_id
            && Nilai::where('siswa_kelas_id', $sk->id)->exists()) {
            return back()->withInput()->withErrors(['kelas_id' => 'Kelas tidak bisa diubah karena siswa sudah punya nilai.']);
        }

        DB::transaction(function () use ($siswa, $sk, $data) {
            $siswa->update(['nis' => $data['nis'], 'nama' => $data['nama']]);
            User::where('siswa_id', $siswa->id)->update(['name' => $data['nama']]);
            if ($sk && ! empty($data['kelas_id'])) {
                $sk->update(['kelas_id' => $data['kelas_id']]);
            }
        });

        return redirect()->route('adm.siswa.index')->with('sukses', 'Data siswa disimpan.');
    }

    private function milik(Siswa $s): void
    {
        abort_unless($s->school_id === auth()->user()->school_id, 403);
    }

    private function kelasAktif(Siswa $s): ?SiswaKelas
    {
        return SiswaKelas::where('siswa_id', $s->id)->whereHas('tahunAjaran', fn ($t) => $t->where('aktif', true))->first();
    }

    private function dataForm(?int $kelasDipilih): array
    {
        $sekolah = auth()->user()->school;
        $kelasData = Kelas::where('school_id', $sekolah->id)->orderBy('tingkat')->get()->map(fn ($k) => [
            'id' => $k->id, 'school_id' => $k->school_id, 'label' => $k->nama, 'terpakai' => null,
        ])->values()->all();

        return ['sekolah' => $sekolah, 'kelasData' => $kelasData, 'kelasDipilih' => old('kelas_id', $kelasDipilih)];
    }
}
