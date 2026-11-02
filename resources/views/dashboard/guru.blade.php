@extends('layouts.app')
@section('title', 'Dashboard Wali Kelas')
@section('heading', $kelas->nama . ' · ' . $kelas->school->nama)
@section('sub', 'Tahun ajaran ' . ($ta ? $ta->nama . ' · ' . $ta->semester : 'belum diatur'))
@section('content')
<div class="stats">
  <x-stat icon="graduation-cap" label="Siswa di kelas ini" :value="$jumlahSiswa" />
  <x-stat icon="book-open" label="Mata pelajaran" :value="$mapel->count()" tone="blue" />
  <x-stat icon="trending-up" label="Rata-rata nilai akhir kelas" :value="$rataKelas ?: '—'" tone="gold" />
</div>
<div class="card">
  <h2>Input nilai per mata pelajaran</h2>
  @if($mapel->isEmpty())<x-empty>Belum ada mata pelajaran untuk kelas ini.</x-empty>@else
  <div class="grid2">
    @foreach($mapel->sortBy(fn($m) => $m->mataPelajaran->nama) as $m)
      <a class="kelas-tile" href="{{ route('gradebook.show', $m) }}">
        <b>{{ $m->mataPelajaran->nama }}</b>
        <small>KKM {{ $m->kkm }} ·
          @if($m->belum_lengkap > 0)<span class="badge warn">{{ $m->belum_lengkap }} siswa belum lengkap</span>
          @else<span class="badge">Nilai lengkap</span>@endif</small>
      </a>
    @endforeach
  </div>@endif
</div>
@endsection
