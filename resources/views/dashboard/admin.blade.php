@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('heading', $sekolah->nama)
@section('sub', 'Tahun ajaran ' . ($ta ? $ta->nama . ' · ' . $ta->semester : 'belum diatur'))
@section('content')
<div class="stats">
  <x-stat icon="graduation-cap" label="Siswa" :value="$jumlahSiswa" />
  <x-stat icon="layers" label="Kelas" :value="$jumlahKelas" tone="blue" />
  <x-stat icon="book-open" label="Mata pelajaran" :value="$jumlahMapel" tone="gold" />
  <x-stat icon="triangle-alert" label="Bobot belum 100%" :value="$bobotBelumSiap" :tone="$bobotBelumSiap ? 'warn' : 'green'" note="Mapel yang perlu diperiksa" />
</div>
<div class="card">
  <h2>Kelola data</h2>
  <div class="grid2">
    <a class="kelas-tile" href="{{ route('adm.guru.index') }}"><b>Guru</b><small>Akun wali kelas di sekolah Anda</small></a>
    <a class="kelas-tile" href="{{ route('adm.siswa.index') }}"><b>Siswa</b><small>Tambah dan ubah data siswa</small></a>
    <a class="kelas-tile" href="{{ route('adm.kelas.index') }}"><b>Kelas</b><small>Daftar kelas dan wali kelas</small></a>
    <a class="kelas-tile" href="{{ route('adm.mapel.index') }}"><b>Mata pelajaran</b><small>Tambah dan ubah mapel</small></a>
    <a class="kelas-tile" href="{{ route('komponen.index') }}"><b>Komponen nilai</b><small>Atur komponen, bobot, dan KKM</small></a>
  </div>
</div>
@endsection
