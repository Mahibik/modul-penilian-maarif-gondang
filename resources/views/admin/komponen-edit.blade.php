@extends('layouts.app')
@section('title', 'Atur Komponen')
@section('heading', $km->mataPelajaran->nama)
@section('sub', $km->kelas->nama . ' — atur komponen penilaian dan bobotnya')
@section('actions')<a class="btn btn-ghost" href="{{ route('komponen.index') }}">Kembali</a>@endsection
@section('content')
@php
  $rows = old('komponen', $km->komponen->map(fn($k) => ['id'=>$k->id,'nama'=>$k->nama,'jenis'=>$k->jenis,'kategori'=>$k->kategori,'bobot'=>(float)$k->bobot,'aktif'=>$k->aktif])->all());
  $rows = array_values($rows);
@endphp
<form method="post" action="{{ route('komponen.update', $km) }}" id="kf">
  @csrf @method('PUT')
  <div class="card">
    <div class="grid2" style="align-items:end">
      <div><label for="kkm">KKM mata pelajaran</label>
        <input id="kkm" type="number" name="kkm" min="0" max="100" value="{{ old('kkm', $km->kkm) }}" required></div>
      <div><label>Total bobot komponen aktif</label>
        <div class="final" style="text-align:left;font-size:24px"><span id="total" class="total">0</span>%</div></div>
    </div>
  </div>
  <div class="card">
    <h2>Komponen penilaian</h2>
    <div class="tbl-wrap"><table>
      <thead><tr><th>Nama di gradebook</th><th>Jenis</th><th>Kategori</th><th style="width:110px">Bobot (%)</th><th class="c">Aktif</th><th></th></tr></thead>
      <tbody id="rows"></tbody>
    </table></div>
    <div class="bar" style="margin-top:18px">
      <button type="button" class="btn btn-ghost" id="tambah">Tambah komponen</button>
      <button class="btn btn-primary">Simpan komponen</button>
    </div>
    <p class="muted" style="margin:0">Menghapus komponen juga menghapus nilai siswa pada komponen tersebut. Nonaktifkan bila nilainya masih ingin disimpan.</p>
  </div>
</form>
@endsection
@push('scripts')
<script>
(() => {
  const jenis = @json($jenis), kategori = @json($kategori), data = @json($rows);
  const tb = document.getElementById('rows'); let idx = 0;
  const opsi = (obj, sel) => (Array.isArray(obj) ? obj.map(v => [v, v]) : Object.entries(obj))
    .map(([v, l]) => `<option value="${v}" ${v === sel ? 'selected' : ''}>${l}</option>`).join('');
  const esc = s => String(s ?? '').replace(/"/g, '&quot;');
  function tambah(r = {}) {
    const i = idx++, tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="hidden" name="komponen[${i}][id]" value="${r.id ?? ''}">
          <input type="text" name="komponen[${i}][nama]" value="${esc(r.nama)}" maxlength="100" required></td>
      <td><select name="komponen[${i}][jenis]">${opsi(jenis, r.jenis)}</select></td>
      <td><select name="komponen[${i}][kategori]">${opsi(kategori, r.kategori || 'pengetahuan')}</select></td>
      <td><input type="number" class="bw" name="komponen[${i}][bobot]" min="0" max="100" step="0.01" value="${r.bobot ?? 0}" required></td>
      <td class="c"><input type="hidden" name="komponen[${i}][aktif]" value="0">
          <input type="checkbox" class="ak" name="komponen[${i}][aktif]" value="1" ${r.aktif === false || r.aktif === '0' ? '' : 'checked'}></td>
      <td class="right"><button type="button" class="btn btn-ghost btn-sm hapus">Hapus</button></td>`;
    tb.appendChild(tr); hitung();
  }
  function hitung() {
    let t = 0;
    tb.querySelectorAll('tr').forEach(tr => { if (tr.querySelector('.ak').checked) t += parseFloat(tr.querySelector('.bw').value || 0); });
    t = Math.round(t * 100) / 100;
    const el = document.getElementById('total'); el.textContent = t;
    el.className = 'total ' + (Math.abs(t - 100) < 0.01 ? 'ok' : 'no');
  }
  tb.addEventListener('input', hitung); tb.addEventListener('change', hitung);
  tb.addEventListener('click', e => {
    if (!e.target.classList.contains('hapus')) return;
    const tr = e.target.closest('tr');
    const hapusBaris = () => { tr.remove(); hitung(); };
    if (tr.querySelector('[name$="[id]"]').value) konfirmasi('Hapus komponen ini beserta nilai siswa di dalamnya?', hapusBaris);
    else hapusBaris();
  });
  document.getElementById('tambah').onclick = () => tambah({nama: '', jenis: jenis[0], bobot: 0});
  data.forEach(tambah); if (!data.length) tambah({nama: 'Tugas', jenis: jenis[0], bobot: 100});
})();
</script>
@endpush
