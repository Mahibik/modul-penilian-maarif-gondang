<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan nilai {{ $km->mataPelajaran->nama }} — {{ $km->kelas->nama }}</title>
<link rel="stylesheet" href="{{ asset('css/maarif.css') }}?v=5">
<style>
  body{font-family:'Plus Jakarta Sans',Arial,sans-serif;color:#111;margin:32px}
  .kop{text-align:center;border-bottom:3px double #0b3b2c;padding-bottom:12px;margin-bottom:20px}
  .kop h1{margin:0;font-size:20px;color:#0b3b2c} .kop p{margin:2px 0;font-size:13px}
  table{border-collapse:collapse;width:100%;font-size:13px} th,td{border:1px solid #444;padding:6px 8px} th{background:#e3efe9}
  td.c,th.c{text-align:center} .meta{font-size:13px;margin-bottom:12px} .meta td{border:0;padding:1px 8px 1px 0}
  .ttd{margin-top:40px;float:right;text-align:center;font-size:13px;width:220px}
  .noprint{margin-bottom:16px} @media print{.noprint{display:none} body{margin:12mm}}
</style>
</head>
<body>
<div class="noprint"><button onclick="window.print()">Cetak</button></div>
<div class="kop">
  <h1>{{ $km->kelas->school->nama }}</h1>
  <p>Yayasan Ma’arif Gondang</p>
  <p><b>LAPORAN NILAI AKHIR</b></p>
</div>
<table class="meta"><tr><td>Mata pelajaran</td><td>: {{ $km->mataPelajaran->nama }}</td><td>Kelas</td><td>: {{ $km->kelas->nama }}</td></tr>
<tr><td>Tahun ajaran</td><td>: {{ $km->tahunAjaran->nama }} {{ $km->tahunAjaran->semester }}</td><td>KKM</td><td>: {{ $km->kkm }}</td></tr></table>
<table>
  <thead><tr><th class="c">No</th><th>Nama siswa</th>
    @foreach($km->komponen as $k)<th class="c">{{ $k->nama }} ({{ rtrim(rtrim(number_format($k->bobot, 2), '0'), '.') }}%)</th>@endforeach
    <th class="c">Nilai akhir</th><th class="c">Predikat</th><th>Keterangan</th></tr></thead>
  <tbody>
  @foreach($siswaKelas as $i => $sk)
    @php $h = $hasil[$sk->id]; $n = $nilai->get($sk->id, collect()); @endphp
    <tr><td class="c">{{ $i + 1 }}</td><td>{{ $sk->siswa->nama }}</td>
      @foreach($km->komponen as $k)<td class="c">{{ $n->has($k->id) ? rtrim(rtrim(number_format($n[$k->id], 2, '.', ''), '0'), '.') : '-' }}</td>@endforeach
      <td class="c"><b>{{ number_format($h['nilai_akhir'], 1) }}</b></td><td class="c">{{ $h['predikat'] }}</td>
      <td>{{ !$h['lengkap'] ? 'Belum lengkap' : ($h['nilai_akhir'] >= $km->kkm ? 'Tuntas' : 'Perlu perbaikan') }}</td></tr>
  @endforeach
  </tbody>
</table>
<div class="ttd">Gondang, {{ now()->translatedFormat('j F Y') }}<br>Wali Kelas<br><br><br><br>(................................)</div>
</body>
</html>
