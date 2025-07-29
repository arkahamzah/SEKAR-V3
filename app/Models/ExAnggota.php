<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExAnggota extends Model
{
    protected $table = 't_ex_anggota';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'V_NAMA_KARYAWAN',
        'V_SHORT_POSISI',
        'V_SHORT_DIVISI',
        'TGL_KELUAR',
        'DPP',
        'DPW',
        'DPD',
        'V_KOTA_GEDUNG',
        'NO_TELP',
        'CREATED_BY',
        'CREATED_AT'
    ];
}
