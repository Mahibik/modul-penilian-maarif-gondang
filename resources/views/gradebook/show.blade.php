@extends('layouts.app')
@section('title', 'Gradebook ' . $km->mataPelajaran->nama)
@section('heading', $km->mataPelajaran->nama)
@section('sub', $km->kelas->nama . ' · ' . $km->kelas->school->nama . ' · ' . $km->tahunAjaran->nama . ' ' . $km->tahunAjaran->semester . ' · KKM ' . $km->kkm)
@section('actions')
  <a class="btn btn-ghost" href="{{ route('kelas.show', $km->kelas_id) }}"><x-icon name="arrow-left" :size="16" />Kembali</a>
  <a class="btn btn-ghost" href="{{ route('gradebook.cetak', $km) }}" target="_blank" rel="noopener"><x-icon name="printer" :size="16" />Cetak laporan</a>
@endsection
@section('content')
@if(!$bisaEdit)<div class="alert alert-info"><x-icon name="eye" :size="16" />Mode lihat saja. Nilai kelas ini hanya dapat diubah oleh wali kelasnya.</div>@endif
@if(abs($totalBobot - 100) > 0.01)
  <div class="alert alert-err"><x-icon name="triangle-alert" :size="16" />Total bobot komponen aktif adalah {{ rtrim(rtrim(number_format($totalBobot, 2), '0'), '.') }}%, belum 100%. Nilai akhir belum akurat sampai Admin Sekolah melengkapi bobot.</div>
@endif
@if($km->komponen->isEmpty())
  <div class="card"><x-empty title="Belum ada komponen penilaian">Belum ada komponen penilaian aktif untuk mata pelajaran ini. Minta Admin Sekolah menambahkannya.</x-empty></div>
@elseif($siswaKelas->isEmpty())
  <div class="card"><x-empty title="Belum ada siswa">Belum ada siswa yang terdaftar di kelas ini.</x-empty></div>
@else
@php
  $rata = $hasil->avg('nilai_akhir');
  $tuntas = $hasil->filter(fn ($h) => $h['lengkap'] && $h['nilai_akhir'] >= $km->kkm)->count();
  $belum = $hasil->filter(fn ($h) => ! $h['lengkap'])->count();
@endphp
<div class="mini">
  <span>Siswa <b>{{ $siswaKelas->count() }}</b></span>
  <span>Rata-rata nilai akhir <b>{{ number_format((float) $rata, 1) }}</b></span>
  <span>Mencapai KKM <b>{{ $tuntas }}</b></span>
  <span>Nilai belum lengkap <b>{{ $belum }}</b></span>
</div>
<form method="post" action="{{ route('gradebook.store', $km) }}" id="gb">
  @csrf @method('PUT')
  <div class="tbl-wrap gb-wrap"><table class="gb">
    <thead><tr>
      <th class="c">No</th><th>Nama siswa</th>
      @foreach($km->komponen as $k)
        <th class="c">{{ $k->nama }}<small>Bobot {{ rtrim(rtrim(number_format($k->bobot, 2), '0'), '.') }}% · {{ ucfirst($k->kategori) }}</small></th>
      @endforeach
      <th class="c">Nilai akhir</th><th class="c">Predikat</th>
    </tr></thead>
    <tbody>
    @foreach($siswaKelas as $i => $sk)
      @php $h = $hasil[$sk->id]; $n = $nilai->get($sk->id, collect()); @endphp
      <tr data-row>
        <td class="c">{{ $i + 1 }}</td>
        <td>{{ $sk->siswa->nama }}<small class="muted" style="display:block">NIS {{ $sk->siswa->nis }}</small></td>
        @foreach($km->komponen as $k)
          @php $db = $n->has($k->id) ? rtrim(rtrim(number_format($n[$k->id], 2, '.', ''), '0'), '.') : ''; @endphp
          <td class="c"><input class="grade" type="number" inputmode="decimal" min="0" max="100" step="0.01"
            name="nilai[{{ $sk->id }}][{{ $k->id }}]" data-bobot="{{ $k->bobot }}" data-init="{{ $db }}"
            value="{{ old("nilai.{$sk->id}.{$k->id}", $db) }}"
            aria-label="{{ $k->nama }} {{ $sk->siswa->nama }}" @disabled(!$bisaEdit)></td>
        @endforeach
        <td class="final" data-akhir>{{ number_format($h['nilai_akhir'], 1) }}</td>
        <td class="c"><span class="pred" data-pred>{{ $h['predikat'] }}</span></td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  @if($bisaEdit)
    <div class="savebar">
      <span class="muted" id="dirtyInfo">Nilai kosong dihitung 0 pada nilai akhir. Rentang nilai 0–100.</span>
      <button class="btn btn-primary"><x-icon name="save" :size="16" />Simpan nilai</button>
    </div>
  @endif
</form>
@endif
@endsection
@push('scripts')
<script>
(() => {
  const skala = @json($predikat);
  const huruf = n => (skala.find(p => n >= p.min) || {huruf: 'D'}).huruf;
  const info = document.getElementById('dirtyInfo');
  const awal = info ? info.textContent : '';
  let kirim = false;
  const hitungDirty = () => {
    const n = document.querySelectorAll('.grade.dirty').length;
    if (info) info.innerHTML = n ? '<b>' + n + ' nilai diubah</b> dan belum disimpan.' : awal;
    return n;
  };
  document.querySelectorAll('[data-row]').forEach(tr => {
    const inputs = tr.querySelectorAll('.grade');
    const hitung = () => {
      let total = 0;
      inputs.forEach(i => {
        const v = i.value === '' ? 0 : parseFloat(i.value);
        i.classList.toggle('bad', i.value !== '' && (isNaN(v) || v < 0 || v > 100));
        i.classList.toggle('dirty', i.value !== i.dataset.init);
        total += (isNaN(v) ? 0 : v) * parseFloat(i.dataset.bobot) / 100;
      });
      total = Math.round(total * 100) / 100;
      tr.querySelector('[data-akhir]').textContent = total.toFixed(1);
      tr.querySelector('[data-pred]').textContent = huruf(total);
      hitungDirty();
    };
    inputs.forEach(i => i.addEventListener('input', hitung));
  });
  const f = document.getElementById('gb');
  if (f) {
    f.addEventListener('submit', e => {
      if (f.querySelector('.grade.bad')) {
        e.preventDefault();
        toast('Ada nilai di luar rentang 0–100. Perbaiki sebelum menyimpan.', 'error');
        f.querySelector('.grade.bad').focus();
        return;
      }
      kirim = true;
    });
    addEventListener('beforeunload', e => { if (!kirim && hitungDirty() > 0) { e.preventDefault(); e.returnValue = ''; } });
  }
})();
</script>
@endpush
