<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $fillable = ['school_id', 'nama', 'semester', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];

    public function school() { return $this->belongsTo(School::class); }
}
