<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    /**
     * Arahkan model untuk menggunakan view 'v_karyawan_base' yang lebih ringan.
     */
    protected $table = 'v_karyawan_base';

    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi. Termasuk kolom-kolom dari view.
     */
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
        'V_KOTA_GEDUNG',
        'TGL_TERDAFTAR',
        'IURAN_WAJIB',
        'IURAN_SUKARELA',
        'STATUS_ANGGOTA'
    ];

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'nik', 'N_NIK');
    }
}