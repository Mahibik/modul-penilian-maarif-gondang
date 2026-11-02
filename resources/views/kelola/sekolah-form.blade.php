@extends('layouts.app')
@php $edit = (bool) $sekolah; @endphp
@section('title', $edit ? 'Ubah Sekolah' : 'Tambah Sekolah')
@section('heading', $edit ? 'Ubah ' . $sekolah->nama : 'Tambah sekolah')
@section('actions')<a class="btn btn-ghost" href="{{ route('sa.sekolah.index') }}">Kembali</a>@endsection
@section('content')
<form method="post" action="{{ $edit ? route('sa.sekolah.update', $sekolah) : route('sa.sekolah.store') }}" class="card" style="max-width:760px">
  @csrf @if($edit) @method('PUT') @endif
  <div class="form-grid">
    <div class="field"><label for="nama">Nama sekolah</label>
      <input id="nama" name="nama" type="text" value="{{ old('nama', $sekolah->nama ?? '') }}" required></div>
    <div class="field"><label for="jenjang">Jenjang</label>
      @if($edit)
        <input id="jenjang" type="text" value="{{ $sekolah->jenjang }}" disabled>
        <div class="hint">Jenjang tidak bisa diubah setelah sekolah dibuat.</div>
      @else
        <select id="jenjang" name="jenjang" required>
          <option value="">Pilih jenjang</option>
          @foreach(['SD','SMP','SMA'] as $j)<option value="{{ $j }}" @selected(old('jenjang') === $j)>{{ $j }}</option>@endforeach
        </select>
      @endif
    </div>
    <div class="field"><label for="npsn">NPSN (opsional)</label>
      <input id="npsn" name="npsn" type="text" maxlength="20" value="{{ old('npsn', $sekolah->npsn ?? '') }}"></div>
    <div></div>
    <div class="field"><label for="ta_nama">Tahun ajaran aktif</label>
      <input id="ta_nama" name="ta_nama" type="text" placeholder="2026/2027" value="{{ old('ta_nama', $ta->nama ?? '') }}" required>
      @if($edit)<div class="hint">Mengubah ini mengganti nama tahun ajaran yang sedang aktif, bukan membuat tahun ajaran baru.</div>@endif</div>
    <div class="field"><label for="semester">Semester</label>
      <select id="semester" name="semester" required>
        @foreach(['Ganjil','Genap'] as $sm)<option value="{{ $sm }}" @selected(old('semester', $ta->semester ?? 'Ganjil') === $sm)>{{ $sm }}</option>@endforeach
      </select></div>
  </div>
  @unless($edit)
    <div class="alert alert-info" style="margin:6px 0 20px">Saat sekolah dibuat, sistem otomatis menyiapkan kelas sesuai jenjang (SD 1–6, SMP 7–9, SMA 10–12), mata pelajaran tiap kelas, dan komponen penilaian awal dengan total bobot 100%.</div>
  @endunless
  <button class="btn btn-primary">{{ $edit ? 'Simpan perubahan' : 'Tambah sekolah' }}</button>
</form>
@endsection
