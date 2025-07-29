<?php
// app/Models/Karyawan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 't_karyawan';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'V_NAMA_KARYAWAN',
        'V_SHORT_UNIT',
        'V_SHORT_POSISI',
        'C_KODE_POSISI',
        'C_KODE_UNIT',
        'V_SHORT_DIVISI',
        'V_BAND_POSISI',
        'C_KODE_DIVISI',
        'C_PERSONNEL_AREA',
        'C_PERSONNEL_SUB_AREA',
        'V_KOTA_GEDUNG'
    ];

    public function pengurus()
    {
        return $this->hasOne(SekarPengurus::class, 'N_NIK', 'N_NIK');
    }
}
