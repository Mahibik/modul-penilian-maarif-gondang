@extends('layouts.app')
@php $edit = (bool) $mapel; @endphp
@section('title', $edit ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran')
@section('heading', $edit ? 'Ubah ' . $mapel->nama : 'Tambah mata pelajaran')
@section('sub', 'Jenjang ' . $sekolah->jenjang)
@section('actions')<a class="btn btn-ghost" href="{{ route('adm.mapel.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route('adm.mapel.update', $mapel) : route('adm.mapel.store') }}" class="card" style="max-width:640px">
  @csrf @if($edit) @method('PUT') @endif
  <div class="field"><label for="kelompok">Kelompok</label>
    <input id="kelompok" name="kelompok" type="text" list="daftar-kelompok" maxlength="120" value="{{ old('kelompok', $mapel->kelompok ?? '') }}" required>
    <datalist id="daftar-kelompok">@foreach($kelompokAda as $k)<option value="{{ $k }}">@endforeach</datalist>
    <div class="hint">Pilih kelompok yang sudah ada atau ketik kelompok baru.</div></div>
  <div class="field"><label for="nama">Nama mata pelajaran</label>
    <input id="nama" name="nama" type="text" maxlength="150" value="{{ old('nama', $mapel->nama ?? '') }}" required>
    <div class="hint">Menulis "Muatan Lokal" otomatis disimpan sebagai "Bahasa Jawa".</div></div>
  <div class="field"><label class="check"><input type="checkbox" name="is_pilihan" value="1" @checked(old('is_pilihan', $mapel->is_pilihan ?? false))> Mata pelajaran pilihan</label></div>
  @unless($edit)
    <div class="alert alert-info">Mata pelajaran baru otomatis dipasang ke semua kelas dengan komponen awal (total bobot 100%). Sesuaikan di menu Komponen & bobot.</div>
  @else
    <div class="alert alert-info">Mata pelajaran dipakai bersama oleh semua sekolah berjenjang {{ $sekolah->jenjang }}, jadi perubahan nama berlaku untuk semuanya.</div>
  @endunless
  <button class="btn btn-primary">{{ $edit ? 'Simpan perubahan' : 'Tambah mata pelajaran' }}</button>
</form>
@endsection
