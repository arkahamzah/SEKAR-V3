<?php
// app/Models/Karyawan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    // =================================================================
    // KODE ORIGINAL ANDA (TETAP DIPERTAHANKAN)
    // =================================================================
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

    // =================================================================
    // PENAMBAHAN FUNGSI RELASI KE USER (INI SOLUSINYA)
    // =================================================================

    /**
     * Mendefinisikan relasi one-to-one ke model User.
     * Satu Karyawan diasumsikan memiliki satu akun User.
     */
    public function user()
    {
        // Menghubungkan model Karyawan dengan User
        // dimana kolom 'N_NIK' di tabel 't_karyawan'
        // sama dengan kolom 'nik' di tabel 'users'.
        return $this->hasOne(User::class, 'nik', 'N_NIK');
    }
}