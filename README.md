# Sistem Modul Penilaian Sekolah Terpadu — Yayasan Ma'arif Gondang

Tema: **Islamic Modern** (hijau tua, putih, aksen emas). Laravel + MySQL.
Folder ini adalah *overlay*: salin ke atas proyek Laravel baru.

## Instalasi
```bash
composer create-project laravel/laravel penilaian
cp -R <folder-ini>/* penilaian/        # timpa file yang sama (routes/web.php, User.php, Controller.php, DatabaseSeeder.php)
cd penilaian
```
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=penilaian_maarif
DB_USERNAME=root
DB_PASSWORD=
APP_LOCALE=id
```
Buat database `penilaian_maarif`, lalu:
```bash
php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000 — tidak perlu `npm`, CSS ada di `public/css/maarif.css`.

## Akun demo (kata sandi: `password`)
| Peran | Username |
|---|---|
| Superadmin | `superadmin` |
| Admin sekolah | `admin_sd`, `admin_smp`, `admin_sma` |
| Wali kelas | `guru_sd_1`…`guru_sd_6`, `guru_smp_7`…`guru_smp_9`, `guru_sma_10`…`guru_sma_12` |
| Siswa | `siswa1001`, `siswa1002`, … |

## Peta fitur PRD → kode
| PRD | Lokasi |
|---|---|
| Matriks hak akses lintas jenjang | `User::bolehLihatSekolah`, `Kelas::scopeDapatDilihat`, `app/Policies/*` |
| Anti-IDOR | Policy + whitelist ID di `GradebookController@store` & `KomponenController@update` |
| Komponen & bobot (Fitur 1) | `Admin/KomponenController`, view `admin/komponen-*` |
| Gradebook dinamis (Fitur 2) | `GradebookController@show`, `gradebook/show.blade.php` |
| Kalkulasi & predikat (Fitur 3) | `Services/GradeCalculator`, `config/penilaian.php` |
| Rekap (Fitur 4) | tabel `rekap_nilai`, diperbarui tiap simpan; dashboard siswa |
| "Muatan Lokal" → "Bahasa Jawa" | mutator di `Models/MataPelajaran` |
| Tanpa N+1 | gradebook = 5 query tetap; rekap memakai `upsert` massal |

## Kelola data Superadmin (menu "Kelola data")
Sekolah (otomatis menyiapkan kelas, mapel, komponen awal), Admin Sekolah, Guru (1 akun per kelas), Siswa (+ akun login), Yayasan.
Akun dapat dinonaktifkan; kata sandi di-reset lewat form Ubah. Setelah menimpa file, jalankan: `php artisan migrate` (menambah kolom `aktif`).

## Kelola data Admin Sekolah (menu "Kelola data")
Guru (akun wali kelas), Siswa (tambah/ubah + akun login), Kelas (tambah tingkat yang belum ada, ubah nama), Mata pelajaran (tambah/ubah, otomatis dipasang ke semua kelas), Komponen nilai. Semua dibatasi ke sekolah milik admin tersebut (`school_id`); akses data sekolah lain ditolak (403).
