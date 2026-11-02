<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
    public function view(User $u, Kelas $kelas): bool
    {
        return $u->bolehLihatSekolah($kelas->school);
    }
}
