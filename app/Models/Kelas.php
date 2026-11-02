<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['school_id', 'tingkat', 'nama'];

    public function school() { return $this->belongsTo(School::class); }
    public function siswaKelas() { return $this->hasMany(SiswaKelas::class); }
    public function kelasMapel() { return $this->hasMany(KelasMapel::class); }

    /** Kelas yang boleh dilihat user (RBAC lintas jenjang). */
    public function scopeDapatDilihat(Builder $q, User $u): Builder
    {
        return match ($u->role) {
            'superadmin'    => $q,
            'admin_sekolah' => $q->where('school_id', $u->school_id),
            'guru'          => $q->whereHas('school', fn ($s) => $s->whereIn('jenjang', School::jenjangDapatDilihat($u->school->jenjang))),
            default         => $q->whereRaw('1 = 0'),
        };
    }
}
