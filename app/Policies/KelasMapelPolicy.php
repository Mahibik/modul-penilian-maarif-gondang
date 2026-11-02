<?php

namespace App\Policies;

use App\Models\KelasMapel;
use App\Models\User;

/**
 * Proteksi IDOR lintas jenjang. Relasi kelas.school harus sudah di-load.
 */
class KelasMapelPolicy
{
    /** Lihat gradebook (read-only) */
    public function view(User $u, KelasMapel $km): bool
    {
        return $u->bolehLihatSekolah($km->kelas->school);
    }

    /** Edit nilai: hanya wali kelas pemilik kelas tersebut */
    public function update(User $u, KelasMapel $km): bool
    {
        return $u->hasRole('guru') && $u->kelas_id === $km->kelas_id;
    }

    /** Atur komponen & bobot: Admin Sekolah di sekolahnya sendiri */
    public function manageKomponen(User $u, KelasMapel $km): bool
    {
        return $u->hasRole('admin_sekolah') && $u->school_id === $km->kelas->school_id;
    }
}
