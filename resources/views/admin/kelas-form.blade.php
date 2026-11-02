@extends('layouts.app')
@php $edit = (bool) $kelas; @endphp
@section('title', $edit ? 'Ubah Kelas' : 'Tambah Kelas')
@section('heading', $edit ? 'Ubah ' . $kelas->nama : 'Tambah kelas')
@section('sub', $sekolah->nama)
@section('actions')<a class="btn btn-ghost" href="{{ route('adm.kelas.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route('adm.kelas.update', $kelas) : route('adm.kelas.store') }}" class="card" style="max-width:560px">
  @csrf @if($edit) @method('PUT') @endif
  @unless($edit)
    <div class="field"><label for="tingkat">Tingkat</label>
      <select id="tingkat" name="tingkat" required>
        <option value="">Pilih tingkat</option>
        @foreach($sisa as $t)<option value="{{ $t }}" @selected(old('tingkat') == $t)>Tingkat {{ $t }}</option>@endforeach
      </select>
      <div class="hint">Hanya tingkat {{ $sekolah->jenjang }} yang belum punya kelas yang muncul.</div></div>
  @endunless
  <div class="field"><label for="nama">Nama kelas</label>
    <input id="nama" name="nama" type="text" maxlength="50" value="{{ old('nama', $kelas->nama ?? '') }}" required></div>
  @unless($edit)
    <div class="alert alert-info">Kelas baru otomatis diberi seluruh mata pelajaran jenjang beserta komponen penilaian awal.</div>
  @endunless
  <button class="btn btn-primary">{{ $edit ? 'Simpan perubahan' : 'Tambah kelas' }}</button>
</form>
@endsection
