<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yayasan extends Model
{
    protected $table = 'yayasans';
    protected $fillable = ['nama', 'alamat'];

    public function schools() { return $this->hasMany(School::class); }
}
