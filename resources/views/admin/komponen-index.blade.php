@extends('layouts.app')
@section('title', 'Komponen & Bobot')
@section('heading', 'Komponen & bobot penilaian')
@section('sub', $sekolah->nama . ' — total bobot komponen aktif tiap mata pelajaran harus 100%')
@section('content')
@forelse($daftar as $kelas => $rows)
<div class="card">
  <h2>{{ $kelas }}</h2>
  <div class="tbl-wrap"><table>
    <thead><tr><th>Mata pelajaran</th><th class="c">Komponen</th><th class="c">Total bobot aktif</th><th class="c">KKM</th><th></th></tr></thead>
    <tbody>@foreach($rows as $m)
      @php $ok = abs(($m->total_bobot ?? 0) - 100) < 0.01; @endphp
      <tr><td>{{ $m->mataPelajaran->nama }}</td><td class="c">{{ $m->komponen_count }}</td>
        <td class="c total {{ $ok ? 'ok' : 'no' }}">{{ rtrim(rtrim(number_format($m->total_bobot ?? 0, 2), '0'), '.') }}% {!! $ok ? '' : '<span class="badge bad">belum 100%</span>' !!}</td>
        <td class="c">{{ $m->kkm }}</td>
        <td class="right"><a class="btn btn-ghost btn-sm" href="{{ route('komponen.edit', $m) }}">Atur</a></td></tr>
    @endforeach</tbody>
  </table></div>
</div>
@empty
<div class="card"><x-empty>Belum ada mata pelajaran pada tahun ajaran aktif.</x-empty></div>
@endforelse
@endsection
