@extends('layouts.app')
@php $edit = (bool) $akun; @endphp
@section('title', $edit ? 'Ubah Guru' : 'Tambah Guru')
@section('heading', $edit ? 'Ubah akun ' . $akun->name : 'Tambah akun guru')
@section('sub', $sekolah->nama)
@section('actions')<a class="btn btn-ghost" href="{{ route('adm.guru.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route('adm.guru.update', $akun) : route('adm.guru.store') }}" class="card" style="max-width:760px">
  @csrf @if($edit) @method('PUT') @endif
  <input type="hidden" id="school_id" value="{{ $sekolah->id }}">
  <div class="form-grid">
    <div class="field"><label for="name">Nama</label>
      <input id="name" name="name" type="text" value="{{ old('name', $akun->name ?? '') }}" required></div>
    <div class="field"><label for="username">Username</label>
      <input id="username" name="username" type="text" autocomplete="off" value="{{ old('username', $akun->username ?? '') }}" required>
      <div class="hint">Huruf, angka, strip, dan garis bawah. Tanpa spasi.</div></div>
    <div class="field"><label for="kelas_id">Kelas yang diampu</label>
      <select id="kelas_id" name="kelas_id" required><option value="">Pilih kelas</option></select>
      <div class="hint">Setiap kelas hanya bisa punya satu akun wali kelas.</div></div>
    <div class="field"><label for="password">{{ $edit ? 'Kata sandi baru' : 'Kata sandi' }}</label>
      <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" {{ $edit ? '' : 'required' }}>
      <div class="hint">{{ $edit ? 'Kosongkan jika tidak diubah. Isi untuk mereset.' : 'Minimal 8 karakter.' }}</div></div>
  </div>
  <button class="btn btn-primary" style="margin-top:8px">{{ $edit ? 'Simpan perubahan' : 'Buat akun' }}</button>
</form>
@include('kelola._kelas-js')
@endsection
