@extends('layouts.app')
@php $edit = (bool) $siswa; @endphp
@section('title', $edit ? 'Ubah Siswa' : 'Tambah Siswa')
@section('heading', $edit ? 'Ubah data ' . $siswa->nama : 'Tambah siswa')
@section('sub', $sekolah->nama)
@section('actions')<a class="btn btn-ghost" href="{{ route('adm.siswa.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route('adm.siswa.update', $siswa) : route('adm.siswa.store') }}" class="card" style="max-width:760px">
  @csrf @if($edit) @method('PUT') @endif
  <input type="hidden" id="school_id" value="{{ $sekolah->id }}">
  <div class="form-grid">
    <div class="field"><label for="kelas_id">Kelas (tahun ajaran aktif)</label>
      <select id="kelas_id" name="kelas_id" {{ $punyaNilai ? 'disabled' : 'required' }}><option value="">Pilih kelas</option></select>
      @if($punyaNilai)<div class="hint">Kelas dikunci karena siswa sudah punya nilai.</div>@endif</div>
    <div class="field"><label for="nis">NIS</label>
      <input id="nis" name="nis" type="text" maxlength="30" value="{{ old('nis', $siswa->nis ?? '') }}" required></div>
    <div class="field"><label for="nama">Nama lengkap</label>
      <input id="nama" name="nama" type="text" value="{{ old('nama', $siswa->nama ?? '') }}" required></div>
    @unless($edit)
      <div class="field"><label for="password">Kata sandi awal siswa</label>
        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8">
        <div class="hint">Kosongkan untuk memakai NIS sebagai kata sandi awal.</div></div>
      <div class="field"><label>Akun login</label>
        <label class="check"><input type="checkbox" name="buat_akun" value="1" checked> Buat akun login (username siswa + NIS)</label></div>
    @else
      <div class="field"><label>Akun login</label><div>{{ $siswa->user?->username ?? 'Belum ada akun' }}</div></div>
    @endunless
  </div>
  <button class="btn btn-primary" style="margin-top:8px">{{ $edit ? 'Simpan perubahan' : 'Tambah siswa' }}</button>
</form>
@include('kelola._kelas-js')
@endsection
