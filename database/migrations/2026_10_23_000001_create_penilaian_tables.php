<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yayasans', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->text('alamat')->nullable();
            $t->timestamps();
        });

        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $t->string('nama');
            $t->enum('jenjang', ['SD', 'SMP', 'SMA']);
            $t->string('npsn', 20)->nullable();
            $t->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $t->string('nama', 20); // 2026/2027
            $t->enum('semester', ['Ganjil', 'Genap']);
            $t->boolean('aktif')->default(false);
            $t->timestamps();
        });

        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $t->unsignedTinyInteger('tingkat');
            $t->string('nama', 50);
            $t->timestamps();
            $t->unique(['school_id', 'tingkat']);
        });

        Schema::create('siswa', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $t->string('nis', 30);
            $t->string('nama');
            $t->timestamps();
            $t->unique(['school_id', 'nis']);
        });

        Schema::create('siswa_kelas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['siswa_id', 'tahun_ajaran_id']);
            $t->index(['kelas_id', 'tahun_ajaran_id']);
        });

        Schema::create('mata_pelajaran', function (Blueprint $t) {
            $t->id();
            $t->enum('jenjang', ['SD', 'SMP', 'SMA']);
            $t->string('kelompok');
            $t->string('nama');
            $t->boolean('is_pilihan')->default(false);
            $t->timestamps();
        });

        Schema::create('kelas_mapel', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $t->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $t->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $t->unsignedTinyInteger('kkm')->default(75);
            $t->timestamps();
            $t->unique(['kelas_id', 'mata_pelajaran_id', 'tahun_ajaran_id'], 'kelas_mapel_unik');
        });

        Schema::create('komponen_nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_mapel_id')->constrained('kelas_mapel')->cascadeOnDelete();
            $t->string('nama', 100);
            $t->string('jenis', 50);
            $t->enum('kategori', ['pengetahuan', 'keterampilan', 'sikap'])->default('pengetahuan');
            $t->decimal('bobot', 5, 2)->default(0);
            $t->boolean('aktif')->default(true);
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('komponen_nilai_id')->constrained('komponen_nilai')->cascadeOnDelete();
            $t->foreignId('siswa_kelas_id')->constrained('siswa_kelas')->cascadeOnDelete();
            $t->decimal('nilai', 5, 2);
            $t->timestamps();
            $t->unique(['komponen_nilai_id', 'siswa_kelas_id']);
        });

        Schema::create('rekap_nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_mapel_id')->constrained('kelas_mapel')->cascadeOnDelete();
            $t->foreignId('siswa_kelas_id')->constrained('siswa_kelas')->cascadeOnDelete();
            $t->decimal('nilai_akhir', 5, 2)->default(0);
            $t->string('predikat', 3)->nullable();
            $t->boolean('lengkap')->default(false);
            $t->unsignedBigInteger('diubah_oleh')->nullable();
            $t->timestamps();
            $t->unique(['kelas_mapel_id', 'siswa_kelas_id']);
        });
    }

    public function down(): void
    {
        foreach (['rekap_nilai', 'nilai', 'komponen_nilai', 'kelas_mapel', 'mata_pelajaran',
                  'siswa_kelas', 'siswa', 'kelas', 'tahun_ajaran', 'schools', 'yayasans'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
