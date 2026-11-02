@extends('layouts.app')
@section('title', 'Data Yayasan')
@section('heading', 'Data yayasan')
@section('sub', 'Nama dan alamat yang tampil pada laporan')
@section('content')
<form method="post" action="{{ route('sa.yayasan.update') }}" class="card" style="max-width:640px">
  @csrf @method('PUT')
  <div class="field"><label for="nama">Nama yayasan</label>
    <input id="nama" name="nama" type="text" value="{{ old('nama', $yayasan->nama) }}" required></div>
  <div class="field"><label for="alamat">Alamat</label>
    <input id="alamat" name="alamat" type="text" value="{{ old('alamat', $yayasan->alamat) }}"></div>
  <button class="btn btn-primary">Simpan data yayasan</button>
</form>
@endsection
