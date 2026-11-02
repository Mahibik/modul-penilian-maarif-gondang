<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['school_id', 'nis', 'nama'];

    public function school() { return $this->belongsTo(School::class); }
    public function user() { return $this->hasOne(User::class, 'siswa_id'); }
    public function siswaKelas() { return $this->hasMany(SiswaKelas::class); }
}
