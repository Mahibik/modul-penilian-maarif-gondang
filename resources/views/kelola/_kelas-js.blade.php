@push('scripts')
<script>
(() => {
  const data = @json($kelasData);
  let pilih = @json($kelasDipilih);
  const s = document.getElementById('school_id'), k = document.getElementById('kelas_id');
  if (!s || !k) return;
  const esc = t => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
  function isi() {
    k.innerHTML = '<option value="">Pilih kelas</option>' + data.filter(d => String(d.school_id) === s.value)
      .map(d => `<option value="${d.id}" ${String(d.id) === String(pilih) ? 'selected' : ''} ${d.terpakai ? 'disabled' : ''}>${esc(d.label)}${d.terpakai ? ' (dipakai ' + esc(d.terpakai) + ')' : ''}</option>`).join('');
  }
  s.addEventListener('change', () => { pilih = ''; isi(); });
  isi();
})();
</script>
@endpush
