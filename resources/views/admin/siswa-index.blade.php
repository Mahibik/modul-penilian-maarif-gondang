@extends('layouts.app')
@section('title', 'Siswa')
@section('heading', 'Siswa')
@section('sub', 'Siswa di sekolah Anda, dengan kelas pada tahun ajaran aktif')
@section('content')
<form method="get" class="filter">
  <div class="f"><label for="q">Cari nama atau NIS</label><input id="q" name="q" type="text" value="{{ request('q') }}"></div>
  <div class="f"><label for="fk">Kelas</label>
    <select id="fk" name="kelas_id"><option value="">Semua kelas</option>
      @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>@endforeach
    </select></div>
  <div class="f sm"><button class="btn btn-ghost">Terapkan</button></div>
  <a class="btn btn-primary push" href="{{ route('adm.siswa.create') }}"><x-icon name="plus" :size="16" />Tambah siswa</a>
</form>
<div class="card">
  @if($siswa->isEmpty())<x-empty>Tidak ada siswa yang cocok. Klik Tambah siswa untuk memasukkan siswa baru.</x-empty>@else
  <div class="tbl-wrap"><table>
    <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Akun</th><th></th></tr></thead>
    <tbody>@foreach($siswa as $s)
      <tr><td>{{ $s->nis }}</td><td>{{ $s->nama }}</td>
        <td>{{ $s->siswaKelas->first()?->kelas?->nama ?? '—' }}</td>
        <td>@if($s->user){{ $s->user->username }}@else<span class="muted">belum ada</span>@endif</td>
        <td><div class="actions"><a class="btn btn-ghost btn-sm" href="{{ route('adm.siswa.edit', $s) }}">Ubah</a></div></td></tr>
    @endforeach</tbody>
  </table></div>
  {{ $siswa->links('vendor.pagination.maarif') }}@endif
</div>
@endsection
