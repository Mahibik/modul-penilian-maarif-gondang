@extends('layouts.app')
@section('title', 'Kelas')
@section('heading', 'Kelas')
@section('sub', $sekolah->nama . ' · jenjang ' . $sekolah->jenjang)
@section('actions')@if(count($sisa))<a class="btn btn-primary" href="{{ route('adm.kelas.create') }}"><x-icon name="plus" :size="16" />Tambah kelas</a>@endif @endsection
@section('content')
<div class="card">
  @if($kelas->isEmpty())<x-empty>Belum ada kelas.</x-empty>@else
  <div class="tbl-wrap"><table>
    <thead><tr><th>Kelas</th><th>Wali kelas</th><th class="c">Siswa</th><th class="c">Mata pelajaran</th><th></th></tr></thead>
    <tbody>@foreach($kelas as $k)
      <tr><td>{{ $k->nama }}</td>
        <td>{!! $wali->has($k->id) ? e($wali[$k->id]) : '<span class="badge bad">belum ada akun</span>' !!}</td>
        <td class="c">{{ $k->siswa_count }}</td><td class="c">{{ $k->mapel_count }}</td>
        <td><div class="actions"><a class="btn btn-ghost btn-sm" href="{{ route('adm.kelas.edit', $k) }}">Ubah nama</a></div></td></tr>
    @endforeach</tbody>
  </table></div>@endif
  @if(!count($sisa))<p class="muted" style="margin:14px 0 0">Semua tingkat {{ $sekolah->jenjang }} sudah memiliki kelas.</p>@endif
</div>
@endsection
