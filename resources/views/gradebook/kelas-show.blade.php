@extends('layouts.app')
@section('title', $kelas->nama)
@section('heading', $kelas->nama . ' · ' . $kelas->school->nama)
@section('sub', 'Tahun ajaran ' . ($ta ? $ta->nama . ' · ' . $ta->semester : 'belum diatur'))
@section('actions')<a class="btn btn-ghost" href="{{ route('kelas.index') }}">Kembali ke daftar kelas</a>@endsection
@section('content')
@php $bisaEdit = $user->hasRole('guru') && $user->kelas_id === $kelas->id; @endphp
<div class="card">
  <h2>Mata pelajaran</h2>
  @if($mapel->isEmpty())<x-empty>Belum ada mata pelajaran pada tahun ajaran aktif.</x-empty>@else
  <div class="grid2">
    @foreach($mapel as $m)
      <a class="kelas-tile" href="{{ route('gradebook.show', $m) }}">
        <b>{{ $m->mataPelajaran->nama }}</b>
        <small>KKM {{ $m->kkm }} · {!! $bisaEdit ? '<span class="badge gold">Input nilai</span>' : '<span class="badge ro">Hanya lihat</span>' !!}</small>
      </a>
    @endforeach
  </div>@endif
</div>
@endsection
