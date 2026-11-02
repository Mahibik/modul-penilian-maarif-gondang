@extends('layouts.app')
@section('title', 'Mata Pelajaran')
@section('heading', 'Mata pelajaran')
@section('sub', 'Jenjang ' . $sekolah->jenjang . ' · "Muatan Lokal" selalu tampil sebagai "Bahasa Jawa"')
@section('actions')<a class="btn btn-primary" href="{{ route('adm.mapel.create') }}"><x-icon name="plus" :size="16" />Tambah mata pelajaran</a>@endsection
@section('content')
@forelse($mapel as $kelompok => $daftar)
<div class="card">
  <h2>{{ $kelompok }}</h2>
  <div class="tbl-wrap"><table>
    <thead><tr><th>Mata pelajaran</th><th class="c">Dipakai di kelas</th><th></th></tr></thead>
    <tbody>@foreach($daftar as $m)
      <tr><td>{{ $m->nama }} {!! $m->is_pilihan ? '<span class="badge gold">Pilihan</span>' : '' !!}</td>
        <td class="c">{{ $m->kelas_count }}</td>
        <td><div class="actions"><a class="btn btn-ghost btn-sm" href="{{ route('adm.mapel.edit', $m) }}">Ubah</a></div></td></tr>
    @endforeach</tbody>
  </table></div>
</div>
@empty
<div class="card"><x-empty>Belum ada mata pelajaran untuk jenjang ini.</x-empty></div>
@endforelse
@endsection
