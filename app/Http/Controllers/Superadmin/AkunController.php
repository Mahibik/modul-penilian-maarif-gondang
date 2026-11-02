<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kelola akun Admin Sekolah dan Guru (wali kelas). Peran dibawa lewat route default 'role'. */
class AkunController extends Controller
{
    private const KONFIG = [
        'admin_sekolah' => ['judul' => 'Admin Sekolah', 'rute' => 'sa.admin', 'butuhKelas' => false],
        'guru' => ['judul' => 'Guru (wali kelas)', 'rute' => 'sa.guru', 'butuhKelas' => true],
    ];

    private const ATRIBUT = ['name' => 'Nama', 'username' => 'Username', 'school_id' => 'Sekolah', 'kelas_id' => 'Kelas', 'password' => 'Kata sandi'];

    public function index(Request $request, string $role)
    {
        $akun = User::with(['school', 'kelas'])->where('role', $role)
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%$v%")->orWhere('username', 'like', "%$v%")))
            ->when($request->school_id, fn ($q, $v) => $q->where('school_id', $v))
            ->orderBy('school_id')->orderBy('name')->paginate(15)->withQueryString();

        return view('kelola.akun-index', ['konf' => self::KONFIG[$role], 'akun' => $akun, 'sekolah' => School::orderBy('id')->get()]);
    }

    public function create(string $role)
    {
        return view('kelola.akun-form', $this->dataForm($role, null));
    }

    public function store(Request $request, string $role)
    {
        $data = $request->validate($this->aturan($role, $request, null), [
            'unique' => ':attribute sudah dipakai.',
            'kelas_id.unique' => 'Kelas ini sudah punya akun wali kelas.',
        ], self::ATRIBUT);

        User::create($data + ['role' => $role, 'aktif' => true]);

        return redirect()->route(self::KONFIG[$role]['rute'] . '.index')->with('sukses', 'Akun ditambahkan.');
    }

    public function edit(string $role, User $user)
    {
        abort_unless($user->role === $role, 404);

        return view('kelola.akun-form', $this->dataForm($role, $user));
    }

    public function update(Request $request, string $role, User $user)
    {
        abort_unless($user->role === $role, 404);

        $data = $request->validate($this->aturan($role, $request, $user), [
            'unique' => ':attribute sudah dipakai.',
            'kelas_id.unique' => 'Kelas ini sudah punya akun wali kelas.',
        ], self::ATRIBUT);

        if (empty($data['password'])) {
            unset($data['password']); // kosong = kata sandi tidak diubah
        }
        $user->update($data);

        return redirect()->route(self::KONFIG[$role]['rute'] . '.index')->with('sukses', 'Akun diperbarui.');
    }

    public function status(string $role, User $user)
    {
        abort_unless($user->role === $role, 404);
        $user->update(['aktif' => ! $user->aktif]);

        return back()->with('sukses', $user->aktif ? 'Akun diaktifkan.' : 'Akun dinonaktifkan. Pengguna tidak bisa masuk lagi.');
    }

    private function aturan(string $role, Request $request, ?User $user): array
    {
        $aturan = [
            'name' => 'required|string|max:100',
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'school_id' => 'required|exists:schools,id',
            'password' => $user ? 'nullable|string|min:8' : 'required|string|min:8',
        ];

        if (self::KONFIG[$role]['butuhKelas']) {
            $aturan['kelas_id'] = [
                'required',
                Rule::exists('kelas', 'id')->where('school_id', $request->input('school_id')),
                Rule::unique('users', 'kelas_id')->where('role', 'guru')->ignore($user?->id),
            ];
        }

        return $aturan;
    }

    private function dataForm(string $role, ?User $user): array
    {
        $dipakai = User::where('role', 'guru')->whereNotNull('kelas_id')
            ->when($user, fn ($q) => $q->where('id', '!=', $user->id))->pluck('name', 'kelas_id');

        $kelasData = Kelas::with('school')->orderBy('school_id')->orderBy('tingkat')->get()->map(fn ($k) => [
            'id' => $k->id, 'school_id' => $k->school_id,
            'label' => $k->nama . ' — ' . $k->school->jenjang, 'terpakai' => $dipakai->get($k->id),
        ])->values()->all();

        return [
            'konf' => self::KONFIG[$role], 'akun' => $user, 'sekolah' => School::orderBy('id')->get(),
            'kelasData' => $kelasData, 'kelasDipilih' => old('kelas_id', $user?->kelas_id),
        ];
    }
}
