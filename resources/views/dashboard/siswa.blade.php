@extends('layouts.app')
@section('title', 'Nilai Saya')
@section('heading', $siswa->nama)
@section('sub', 'NIS ' . $siswa->nis . ' · ' . $sk->kelas->nama . ' · ' . $sk->tahunAjaran->nama . ' ' . $sk->tahunAjaran->semester)
@section('content')
<div class="stats">
  <x-stat icon="trending-up" label="Rata-rata nilai akhir" :value="$rata ?: '—'" />
  <x-stat icon="target" label="Mapel mencapai KKM" :value="$tuntas . ' / ' . $baris->count()" tone="gold" />
</div>
<div class="card">
  <h2>Rekap nilai</h2>
  <div class="tbl-wrap"><table>
    <thead><tr><th>Mata pelajaran</th><th class="c">Pengetahuan</th><th class="c">Sikap</th><th class="c">Nilai akhir</th><th class="c">Predikat</th><th>Keterangan</th></tr></thead>
    <tbody>@foreach($baris as $b)
      <tr>
        <td>{{ $b['mapel'] }}<small class="muted" style="display:block">KKM {{ $b['kkm'] }}</small></td>
        @foreach(['pengetahuan','sikap'] as $k)<td class="c">{{ $b['kategori'][$k] ?? '—' }}</td>@endforeach
        <td class="final">{{ $b['akhir'] !== null ? number_format($b['akhir'], 1) : '—' }}</td>
        <td class="c">@if($b['predikat'])<span class="pred">{{ $b['predikat'] }}</span>@endif</td>
        <td>@if($b['akhir'] === null)<span class="badge neutral">Belum dinilai</span>
          @elseif(!$b['lengkap'])<span class="badge warn">Nilai belum lengkap</span>
          @elseif($b['akhir'] >= $b['kkm'])<span class="badge">Tuntas</span>
          @else<span class="badge bad">Perlu perbaikan</span>@endif</td>
      </tr>
    @endforeach</tbody>
  </table></div>
</div>
@endsection
