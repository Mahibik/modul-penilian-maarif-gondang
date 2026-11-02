@extends('layouts.app')
@section('title', 'Guru')
@section('heading', 'Guru (wali kelas)')
@section('sub', 'Satu akun untuk satu kelas di sekolah Anda')
@section('content')
<form method="get" class="filter">
  <div class="f"><label for="q">Cari nama atau username</label><input id="q" name="q" type="text" value="{{ request('q') }}"></div>
  <div class="f sm"><button class="btn btn-ghost">Cari</button></div>
  <a class="btn btn-primary push" href="{{ route('adm.guru.create') }}"><x-icon name="plus" :size="16" />Tambah akun guru</a>
</form>
<div class="card">
  @if($guru->isEmpty())<x-empty>Belum ada akun guru yang cocok.</x-empty>@else
  <div class="tbl-wrap"><table>
    <thead><tr><th>Nama</th><th>Username</th><th>Kelas</th><th class="c">Status</th><th></th></tr></thead>
    <tbody>@foreach($guru as $g)
      <tr><td>{{ $g->name }}</td><td>{{ $g->username }}</td><td>{{ $g->kelas?->nama ?? '—' }}</td>
        <td class="c">{!! $g->aktif ? '<span class="badge">Aktif</span>' : '<span class="badge bad">Nonaktif</span>' !!}</td>
        <td><div class="actions">
          <a class="btn btn-ghost btn-sm" href="{{ route('adm.guru.edit', $g) }}">Ubah</a>
          <form method="post" action="{{ route('adm.guru.status', $g) }}"
                data-confirm="{{ $g->aktif ? 'Nonaktifkan akun ini? Guru tidak bisa masuk lagi.' : 'Aktifkan kembali akun ini?' }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm {{ $g->aktif ? 'btn-danger' : 'btn-ghost' }}">{{ $g->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
          </form></div></td></tr>
    @endforeach</tbody>
  </table></div>
  {{ $guru->links('vendor.pagination.maarif') }}@endif
</div>
@endsection
