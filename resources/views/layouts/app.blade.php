<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — Penilaian Ma’arif</title>
<link rel="stylesheet" href="{{ asset('css/maarif.css') }}?v=5">
</head>
<body>
@php
  $u = auth()->user();
  $inisial = mb_strtoupper(collect(preg_split('/\s+/', trim($u->name)))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));

  // [rute, label, ikon Lucide, pola rute aktif]; rute null = judul grup
  $menu = [['dashboard', 'Dashboard', 'layout-dashboard', 'dashboard']];
  if ($u->hasRole('guru', 'admin_sekolah', 'superadmin')) {
    $menu[] = ['kelas.index', $u->hasRole('guru') ? 'Kelas & nilai' : ($u->hasRole('superadmin') ? 'Laporan nilai' : 'Daftar kelas'),
               $u->hasRole('guru') ? 'book-open' : 'clipboard-list', 'kelas.*|gradebook.*'];
  }
  if ($u->hasRole('admin_sekolah')) {
    $menu[] = [null, 'Kelola data', null, null];
    $menu[] = ['adm.guru.index', 'Guru', 'users', 'adm.guru.*'];
    $menu[] = ['adm.siswa.index', 'Siswa', 'graduation-cap', 'adm.siswa.*'];
    $menu[] = ['adm.kelas.index', 'Kelas', 'layers', 'adm.kelas.*'];
    $menu[] = ['adm.mapel.index', 'Mata pelajaran', 'book-open', 'adm.mapel.*'];
    $menu[] = ['komponen.index', 'Komponen nilai', 'sliders-horizontal', 'komponen.*'];
  }
  if ($u->hasRole('superadmin')) {
    $menu[] = [null, 'Kelola data', null, null];
    $menu[] = ['sa.sekolah.index', 'Sekolah', 'school', 'sa.sekolah.*'];
    $menu[] = ['sa.admin.index', 'Admin sekolah', 'user-cog', 'sa.admin.*'];
    $menu[] = ['sa.guru.index', 'Guru', 'users', 'sa.guru.*'];
    $menu[] = ['sa.siswa.index', 'Siswa', 'graduation-cap', 'sa.siswa.*'];
    $menu[] = ['sa.yayasan.edit', 'Yayasan', 'building-2', 'sa.yayasan.*'];
  }
@endphp
<div class="shell" id="shell">
  <script>
    // Terapkan preferensi sidebar sebelum render agar tidak berkedip
    (function () {
      var s = document.getElementById('shell'), w = innerWidth, p = null;
      try { p = localStorage.getItem('sb'); } catch (e) {}
      if (w >= 768 && (p === 'collapsed' || (p === null && w < 1100))) s.classList.add('sb-collapsed');
    })();
  </script>
  <aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
    <div class="sb-brand">
      <span class="logo"><x-icon name="graduation-cap" :size="18" /></span>
      <div><b>Penilaian Ma’arif</b><small>Yayasan Ma’arif Gondang</small></div>
    </div>
    <nav class="sb-nav">
      @foreach($menu as [$rute, $label, $ic, $pola])
        @if($rute === null)
          <div class="sb-label">{{ $label }}</div>
          @continue
        @endif
        <a href="{{ route($rute) }}" title="{{ $label }}" class="{{ request()->routeIs(...explode('|', $pola)) ? 'on' : '' }}" @if(request()->routeIs(...explode('|', $pola))) aria-current="page" @endif>
          <x-icon :name="$ic" :size="18" /><span class="sb-text">{{ $label }}</span>
        </a>
      @endforeach
    </nav>
  </aside>
  <div class="overlay" id="overlay"></div>

  <div class="main">
    <header class="topbar">
      <button type="button" class="icon-btn" id="sbToggle" aria-label="Buka atau tutup menu"><x-icon name="panel-left" :size="18" /></button>
      <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Beranda</a>
        @unless(request()->routeIs('dashboard'))<x-icon name="chevron-right" :size="14" /><span>@yield('heading')</span>@endunless
      </nav>
      <div class="profile" id="profile">
        <button type="button" class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
          <span class="avatar">{{ $inisial }}</span>
          <span class="p-text"><b>{{ $u->name }}</b><small>{{ $u->labelRole() }}@if($u->school) · {{ $u->school->jenjang }}@endif</small></span>
          <x-icon name="chevron-down" :size="14" class="muted" />
        </button>
        <div class="menu" id="profileMenu" role="menu">
          <div class="menu-head"><b>{{ $u->name }}</b><small>{{ '@' . $u->username }} · {{ $u->labelRole() }}</small></div>
          <form method="post" action="{{ route('logout') }}">@csrf
            <button class="menu-item danger" role="menuitem"><x-icon name="log-out" :size="16" />Keluar</button>
          </form>
        </div>
      </div>
    </header>

    <main class="content">
      <div class="page-head">
        <div>
          <h1>@yield('heading')</h1>
          @hasSection('sub')<div class="sub">@yield('sub')</div>@endif
        </div>
        <div class="page-actions">@yield('actions')</div>
      </div>
      @if($errors->any())
        <div class="alert alert-err" role="alert"><x-icon name="circle-x" :size="16" />
          <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
    </main>
  </div>
</div>

<dialog id="konfirmasi" aria-labelledby="kTitle">
  <h3 id="kTitle">Konfirmasi</h3>
  <p id="kText"></p>
  <div class="actions">
    <button type="button" class="btn btn-ghost" id="kBatal">Batal</button>
    <button type="button" class="btn btn-primary" id="kOk">Lanjutkan</button>
  </div>
</dialog>
<div id="toasts" aria-live="polite"></div>
<div id="progress"></div>

@stack('scripts')
<script>
(() => {
  const $ = id => document.getElementById(id), shell = $('shell');
  const mobile = () => innerWidth < 768;

  // Sidebar: desktop/tablet = ciut/lebar, mobile = drawer
  $('sbToggle').addEventListener('click', () => {
    if (mobile()) { shell.classList.toggle('sb-open'); return; }
    const c = shell.classList.toggle('sb-collapsed');
    try { localStorage.setItem('sb', c ? 'collapsed' : 'expanded'); } catch (e) {}
  });
  $('overlay').addEventListener('click', () => shell.classList.remove('sb-open'));

  // Menu profil
  const pb = $('profileBtn'), pm = $('profileMenu');
  const tutup = () => { pm.classList.remove('open'); pb.setAttribute('aria-expanded', 'false'); };
  pb.addEventListener('click', e => { e.stopPropagation(); const o = pm.classList.toggle('open'); pb.setAttribute('aria-expanded', o); });
  document.addEventListener('click', e => { if (!$('profile').contains(e.target)) tutup(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { tutup(); shell.classList.remove('sb-open'); } });

  // Toast
  const ikon = { ok: '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
    error: '<svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>' };
  window.toast = (pesan, tipe = 'ok') => {
    const t = document.createElement('div');
    t.className = 'toast ' + (tipe === 'error' ? 'error' : '');
    t.setAttribute('role', tipe === 'error' ? 'alert' : 'status');
    t.innerHTML = ikon[tipe === 'error' ? 'error' : 'ok'] + '<span></span><button type="button" aria-label="Tutup">&times;</button>';
    t.querySelector('span').textContent = pesan;
    t.querySelector('button').onclick = () => t.remove();
    $('toasts').appendChild(t);
    setTimeout(() => t.remove(), tipe === 'error' ? 7000 : 4500);
  };
  @if(session('sukses'))toast(@json(session('sukses')));@endif

  // Dialog konfirmasi (menggantikan confirm bawaan browser)
  const dlg = $('konfirmasi');
  window.konfirmasi = (pesan, aksi, bahaya = true, judul = 'Konfirmasi') => {
    $('kTitle').textContent = judul; $('kText').textContent = pesan;
    $('kOk').className = 'btn ' + (bahaya ? 'btn-primary' : 'btn-primary');
    dlg.showModal();
    $('kOk').onclick = () => { dlg.close(); aksi(); };
  };
  $('kBatal').onclick = () => dlg.close();
  document.addEventListener('submit', e => {
    const f = e.target;
    if (f.dataset.confirm) { e.preventDefault(); konfirmasi(f.dataset.confirm, () => f.submit(), true); return; }
    const b = e.submitter || f.querySelector('button:not([type=button])');
    if (b && !f.dataset.confirm) { setTimeout(() => { if (!e.defaultPrevented) { b.classList.add('loading'); document.body.classList.add('is-loading'); } }, 0); }
  });

  // Indikator loading saat berpindah halaman
  document.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || a.href.startsWith('javascript') || a.getAttribute('href').startsWith('#') || a.origin !== location.origin) return;
    document.body.classList.add('is-loading');
  });
  addEventListener('pageshow', () => { document.body.classList.remove('is-loading'); document.querySelectorAll('.btn.loading').forEach(b => b.classList.remove('loading')); });
})();
</script>
</body>
</html>
