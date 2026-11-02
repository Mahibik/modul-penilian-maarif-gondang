<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('email')->nullable()->change();
            $t->string('username')->unique()->after('name');
            $t->enum('role', ['superadmin', 'admin_sekolah', 'guru', 'siswa'])->default('siswa')->after('password');
            $t->foreignId('school_id')->nullable()->after('role')->constrained('schools')->nullOnDelete();
            $t->foreignId('kelas_id')->nullable()->after('school_id')->constrained('kelas')->nullOnDelete();
            $t->foreignId('siswa_id')->nullable()->after('kelas_id')->constrained('siswa')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('siswa_id');
            $t->dropConstrainedForeignId('kelas_id');
            $t->dropConstrainedForeignId('school_id');
            $t->dropColumn(['username', 'role']);
        });
    }
};
