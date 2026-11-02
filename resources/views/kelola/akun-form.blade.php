@extends('layouts.app')
@php $edit = (bool) $akun; @endphp
@section('title', ($edit ? 'Ubah ' : 'Tambah ') . $konf['judul'])
@section('heading', $edit ? 'Ubah akun ' . $akun->name : 'Tambah akun ' . $konf['judul'])
@section('actions')<a class="btn btn-ghost" href="{{ route($konf['rute'] . '.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route($konf['rute'] . '.update', $akun) : route($konf['rute'] . '.store') }}" class="card" style="max-width:760px">
  @csrf @if($edit) @method('PUT') @endif
  <div class="form-grid">
    <div class="field"><label for="name">Nama</label>
      <input id="name" name="name" type="text" value="{{ old('name', $akun->name ?? '') }}" required></div>
    <div class="field"><label for="username">Username</label>
      <input id="username" name="username" type="text" autocomplete="off" value="{{ old('username', $akun->username ?? '') }}" required>
      <div class="hint">Huruf, angka, strip, dan garis bawah. Tanpa spasi.</div></div>
    <div class="field"><label for="school_id">Sekolah</label>
      <select id="school_id" name="school_id" required>
        <option value="">Pilih sekolah</option>
        @foreach($sekolah as $s)<option value="{{ $s->id }}" @selected(old('school_id', $akun->school_id ?? '') == $s->id)>{{ $s->nama }}</option>@endforeach
      </select></div>
    @if($konf['butuhKelas'])
      <div class="field"><label for="kelas_id">Kelas yang diampu</label>
        <select id="kelas_id" name="kelas_id" required><option value="">Pilih kelas</option></select>
        <div class="hint">Setiap kelas hanya bisa punya satu akun wali kelas.</div></div>
    @endif
    <div class="field"><label for="password">{{ $edit ? 'Kata sandi baru' : 'Kata sandi' }}</label>
      <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" {{ $edit ? '' : 'required' }}>
      <div class="hint">{{ $edit ? 'Kosongkan jika kata sandi tidak diubah. Isi untuk mereset.' : 'Minimal 8 karakter.' }}</div></div>
  </div>
  <button class="btn btn-primary" style="margin-top:8px">{{ $edit ? 'Simpan perubahan' : 'Buat akun' }}</button>
</form>
@if($konf['butuhKelas'])@include('kelola._kelas-js')@endif
@endsection
