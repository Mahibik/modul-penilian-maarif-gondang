@extends('layouts.app')
@section('title', $konf['judul'])
@section('heading', $konf['judul'])
@section('sub', $konf['butuhKelas'] ? 'Satu akun untuk satu kelas: SD 6 akun, SMP 3 akun, SMA 3 akun' : 'Pengelola data penilaian di tiap sekolah')
@section('content')
<form method="get" class="filter">
  <div class="f"><label for="q">Cari nama atau username</label><input id="q" name="q" type="text" value="{{ request('q') }}"></div>
  <div class="f"><label for="fs">Sekolah</label>
    <select id="fs" name="school_id"><option value="">Semua sekolah</option>
      @foreach($sekolah as $s)<option value="{{ $s->id }}" @selected(request('school_id') == $s->id)>{{ $s->nama }}</option>@endforeach
    </select></div>
  <div class="f sm"><button class="btn btn-ghost">Terapkan</button></div>
  <a class="btn btn-primary push" href="{{ route($konf['rute'] . '.create') }}"><x-icon name="plus" :size="16" />Tambah akun</a>
</form>
<div class="card">
  @if($akun->isEmpty())<x-empty>Belum ada akun yang cocok. Klik Tambah akun untuk membuat yang baru.</x-empty>@else
  <div class="tbl-wrap"><table>
    <thead><tr><th>Nama</th><th>Username</th><th>Sekolah</th>@if($konf['butuhKelas'])<th>Kelas</th>@endif<th class="c">Status</th><th></th></tr></thead>
    <tbody>@foreach($akun as $a)
      <tr><td>{{ $a->name }}</td><td>{{ $a->username }}</td><td>{{ $a->school?->nama ?? '—' }}</td>
        @if($konf['butuhKelas'])<td>{{ $a->kelas?->nama ?? '—' }}</td>@endif
        <td class="c">{!! $a->aktif ? '<span class="badge">Aktif</span>' : '<span class="badge bad">Nonaktif</span>' !!}</td>
        <td><div class="actions">
          <a class="btn btn-ghost btn-sm" href="{{ route($konf['rute'] . '.edit', $a) }}">Ubah</a>
          <form method="post" action="{{ route($konf['rute'] . '.status', $a) }}"
                data-confirm="{{ $a->aktif ? 'Nonaktifkan akun ini? Pengguna tidak bisa masuk lagi.' : 'Aktifkan kembali akun ini?' }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm {{ $a->aktif ? 'btn-danger' : 'btn-ghost' }}">{{ $a->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
          </form></div></td></tr>
    @endforeach</tbody>
  </table></div>
  {{ $akun->links('vendor.pagination.maarif') }}@endif
</div>
@endsection
