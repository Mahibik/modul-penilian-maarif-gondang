<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk — Penilaian Ma’arif</title>
<link rel="stylesheet" href="{{ asset('css/maarif.css') }}?v=5">
</head>
<body>
<div class="login">
  <section class="login-art">
    <div class="brandrow"><span class="logo"><x-icon name="graduation-cap" :size="18" /></span>Penilaian Ma’arif</div>
    <div>
      <div class="arab">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</div>
      <h1>Sistem penilaian terpadu SD, SMP, dan SMA</h1>
      <p>Yayasan Ma’arif Gondang — mencatat, menghitung, dan merekap nilai siswa dalam satu tempat.</p>
    </div>
    <small style="color:#7ea592">© {{ date('Y') }} Yayasan Ma’arif Gondang</small>
  </section>
  <section class="login-form">
    <form method="post" action="{{ url('/login') }}">
      @csrf
      <h2>Masuk ke akun Anda</h2>
      <p class="muted" style="margin-bottom:16px">Gunakan username yang diberikan sekolah.</p>
      @if($errors->any())<div class="alert alert-err" role="alert"><x-icon name="circle-x" :size="16" />{{ $errors->first() }}</div>@endif
      <div class="field"><label for="username">Username</label>
        <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus></div>
      <div class="field"><label for="password">Kata sandi</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required></div>
      <label class="check" style="margin-bottom:16px"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label>
      <button class="btn btn-primary" style="width:100%;height:40px">Masuk</button>
      <p class="demo">Akun demo (kata sandi <b>password</b>): superadmin, admin_sd, guru_sd_1, guru_smp_7, guru_sma_10, siswa1001</p>
    </form>
  </section>
</div>
<script>
  document.querySelector('form').addEventListener('submit', e => e.target.querySelector('button.btn').classList.add('loading'));
  addEventListener('pageshow', () => document.querySelectorAll('.btn.loading').forEach(b => b.classList.remove('loading')));
</script>
</body>
</html>
