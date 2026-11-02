@extends('layouts.app')
@section('title', 'Kelola Sekolah')
@section('heading', 'Sekolah')
@section('sub', 'SD, SMP, dan SMA di bawah yayasan')
@section('actions')<a class="btn btn-primary" href="{{ route('sa.sekolah.create') }}"><x-icon name="plus" :size="16" />Tambah sekolah</a>@endsection
@section('content')
<div class="card">
  @if($sekolah->isEmpty())<x-empty>Belum ada sekolah. Klik Tambah sekolah untuk memulai.</x-empty>@else
  <div class="tbl-wrap"><table>
    <thead><tr><th>Sekolah</th><th class="c">Jenjang</th><th>NPSN</th><th>Tahun ajaran aktif</th><th class="c">Kelas</th><th class="c">Siswa</th><th></th></tr></thead>
    <tbody>@foreach($sekolah as $s)
      @php $ta = $s->tahunAjaran->first(); @endphp
      <tr><td>{{ $s->nama }}</td><td class="c"><span class="badge gold">{{ $s->jenjang }}</span></td>
        <td>{{ $s->npsn ?: '—' }}</td>
        <td>{!! $ta ? e($ta->nama . ' · ' . $ta->semester) : '<span class="badge bad">belum diatur</span>' !!}</td>
        <td class="c">{{ $s->kelas_count }}</td><td class="c">{{ $s->siswa_count }}</td>
        <td><div class="actions"><a class="btn btn-ghost btn-sm" href="{{ route('sa.sekolah.edit', $s) }}">Ubah</a></div></td></tr>
    @endforeach</tbody>
  </table></div>@endif
</div>
@endsection
