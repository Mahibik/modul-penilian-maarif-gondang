<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'school_id', 'kelas_id', 'siswa_id', 'aktif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean'];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class); }
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class); }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Matriks hak lihat lintas jenjang (PRD 3.B):
     * SD -> SD | SMP -> SD,SMP | SMA -> SD,SMP,SMA
     */
    public function bolehLihatSekolah(School $target): bool
    {
        return match ($this->role) {
            'superadmin'    => true,
            'admin_sekolah' => $this->school_id === $target->id,
            'guru'          => School::RANK[$target->jenjang] <= School::RANK[$this->school->jenjang],
            default         => false,
        };
    }

    public function labelRole(): string
    {
        return ['superadmin' => 'Superadmin Yayasan', 'admin_sekolah' => 'Admin Sekolah', 'guru' => 'Wali Kelas', 'siswa' => 'Siswa'][$this->role] ?? $this->role;
    }
}
