<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin Sekolah mengelola akun wali kelas di sekolahnya sendiri. */
class GuruController extends Controller
{
    private const ATRIBUT = ['name' => 'Nama', 'username' => 'Username', 'kelas_id' => 'Kelas', 'password' => 'Kata sandi'];

    public function index(Request $request)
    {
        $guru = User::with('kelas')->where('role', 'guru')->where('school_id', auth()->user()->school_id)
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%$v%")->orWhere('username', 'like', "%$v%")))
            ->orderBy('kelas_id')->paginate(15)->withQueryString();

        return view('admin.guru-index', ['guru' => $guru]);
    }

    public function create()
    {
        return view('admin.guru-form', $this->dataForm(null));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan($request, null), $this->pesan(), self::ATRIBUT);
        User::create($data + ['role' => 'guru', 'school_id' => auth()->user()->school_id, 'aktif' => true]);

        return redirect()->route('adm.guru.index')->with('sukses', 'Akun wali kelas ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->milik($user);

        return view('admin.guru-form', $this->dataForm($user));
    }

    public function update(Request $request, User $user)
    {
        $this->milik($user);
        $data = $request->validate($this->aturan($request, $user), $this->pesan(), self::ATRIBUT);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('adm.guru.index')->with('sukses', 'Akun diperbarui.');
    }

    public function status(User $user)
    {
        $this->milik($user);
        $user->update(['aktif' => ! $user->aktif]);

        return back()->with('sukses', $user->aktif ? 'Akun diaktifkan.' : 'Akun dinonaktifkan. Guru tidak bisa masuk lagi.');
    }

    private function milik(User $u): void
    {
        abort_unless($u->role === 'guru' && $u->school_id === auth()->user()->school_id, 403);
    }

    private function aturan(Request $request, ?User $user): array
    {
        return [
            'name' => 'required|string|max:100',
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'kelas_id' => [
                'required',
                Rule::exists('kelas', 'id')->where('school_id', auth()->user()->school_id),
                Rule::unique('users', 'kelas_id')->where('role', 'guru')->ignore($user?->id),
            ],
            'password' => $user ? 'nullable|string|min:8' : 'required|string|min:8',
        ];
    }

    private function pesan(): array
    {
        return ['unique' => ':attribute sudah dipakai.', 'kelas_id.unique' => 'Kelas ini sudah punya akun wali kelas.'];
    }

    private function dataForm(?User $user): array
    {
        $sekolah = auth()->user()->school;
        $dipakai = User::where('role', 'guru')->whereNotNull('kelas_id')->where('school_id', $sekolah->id)
            ->when($user, fn ($q) => $q->where('id', '!=', $user->id))->pluck('name', 'kelas_id');

        $kelasData = Kelas::where('school_id', $sekolah->id)->orderBy('tingkat')->get()->map(fn ($k) => [
            'id' => $k->id, 'school_id' => $k->school_id, 'label' => $k->nama, 'terpakai' => $dipakai->get($k->id),
        ])->values()->all();

        return ['akun' => $user, 'sekolah' => $sekolah, 'kelasData' => $kelasData, 'kelasDipilih' => old('kelas_id', $user?->kelas_id)];
    }
}
