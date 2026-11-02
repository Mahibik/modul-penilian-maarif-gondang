@extends('layouts.app')
@section('title', 'Dashboard Yayasan')
@section('heading', 'Dashboard Yayasan')
@section('sub', 'Ringkasan seluruh sekolah di bawah Yayasan Ma’arif Gondang')
@section('content')
<div class="stats">
  <x-stat icon="school" label="Sekolah" :value="$sekolah->count()" />
  <x-stat icon="layers" label="Kelas" :value="number_format($totalKelas)" tone="blue" />
  <x-stat icon="graduation-cap" label="Siswa terdaftar" :value="number_format($totalSiswa)" tone="gold" />
</div>
<div class="card">
  <h2>Laporan gabungan per sekolah</h2>
  <div class="tbl-wrap"><table>
    <thead><tr><th>Sekolah</th><th class="c">Jenjang</th><th class="c">Kelas</th><th class="c">Siswa</th><th class="c">Rata-rata nilai akhir</th></tr></thead>
    <tbody>@foreach($sekolah as $s)
      <tr><td>{{ $s->nama }}</td><td class="c"><span class="badge info">{{ $s->jenjang }}</span></td>
        <td class="c">{{ $s->kelas_count }}</td><td class="c">{{ $s->siswa_count }}</td>
        <td class="final">{{ $s->rata ?? '—' }}</td></tr>
    @endforeach</tbody>
  </table></div>
</div>
<div class="card">
  <h2>Kelola data</h2>
  <div class="grid2">
    <a class="kelas-tile" href="{{ route('sa.sekolah.index') }}"><b>Sekolah</b><small>Tambah sekolah dan atur tahun ajaran</small></a>
    <a class="kelas-tile" href="{{ route('sa.admin.index') }}"><b>Admin sekolah</b><small>Buat, ubah, dan nonaktifkan akun</small></a>
    <a class="kelas-tile" href="{{ route('sa.guru.index') }}"><b>Guru</b><small>Akun wali kelas per kelas</small></a>
    <a class="kelas-tile" href="{{ route('sa.siswa.index') }}"><b>Siswa</b><small>Data siswa dan penempatan kelas</small></a>
    <a class="kelas-tile" href="{{ route('kelas.index') }}"><b>Laporan nilai</b><small>Lihat nilai tiap kelas (read-only)</small></a>
  </div>
</div>
@endsection
