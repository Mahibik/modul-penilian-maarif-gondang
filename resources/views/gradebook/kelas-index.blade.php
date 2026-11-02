@extends('layouts.app')
@section('title', 'Daftar Kelas')
@section('heading', $user->hasRole('guru') ? 'Kelas & nilai' : 'Daftar kelas')
@section('sub')
  @if($user->hasRole('guru'))Anda dapat mengedit nilai kelas sendiri dan melihat kelas lain sesuai jenjang yang diizinkan.@else Pilih kelas untuk melihat nilai siswa.@endif
@endsection
@section('content')
@forelse($kelasPerSekolah as $nama => $daftar)
  <div class="card">
    <h2>{{ $nama }}</h2>
    <div class="grid2">
      @foreach($daftar as $k)
        <a class="kelas-tile" href="{{ route('kelas.show', $k) }}">
          <b>{{ $k->nama }}</b>
          <small>@if($user->hasRole('guru'))
              @if($user->kelas_id === $k->id)<span class="badge gold">Kelas Anda · bisa edit</span>@else<span class="badge ro">Hanya lihat</span>@endif
            @else {{ $k->school->jenjang }} @endif</small>
        </a>
      @endforeach
    </div>
  </div>
@empty
  <div class="card"><x-empty>Belum ada kelas yang dapat Anda akses.</x-empty></div>
@endforelse
@endsection
