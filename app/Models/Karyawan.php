<?php
// app/Models/Karyawan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    /**
     * Menentukan tabel database yang digunakan oleh model.
     * Diubah dari 't_karyawan' menjadi 'v_karyawan' untuk menggunakan view.
     */
    protected $table = 'v_karyawan';

    /**
     * Menentukan primary key tabel.
     */
    protected $primaryKey = 'ID';

    /**
     * Menonaktifkan timestamps (created_at dan updated_at) karena view
     * dan tabel aslinya tidak memilikinya.
     */
    public $timestamps = false;

    /**
     * Daftar atribut yang dapat diisi secara massal (mass assignable).
     * Kolom DPD dan DPW ditambahkan sesuai dengan struktur v_karyawan.
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
        'DPD', // Ditambahkan dari view v_karyawan
        'DPW'  // Ditambahkan dari view v_karyawan
    ];

    /**
     * Mendefinisikan relasi one-to-one ke model SekarPengurus.
     */
    public function pengurus()
    {
        return $this->hasOne(SekarPengurus::class, 'N_NIK', 'N_NIK');
    }

    /**
     * Mendefinisikan relasi one-to-one ke model User.
     * Satu Karyawan diasumsikan memiliki satu akun User.
     */
    public function user()
    {
        // Menghubungkan model Karyawan dengan User
        // dimana kolom 'N_NIK' di tabel 'v_karyawan' (asalnya t_karyawan)
        // sama dengan kolom 'nik' di tabel 'users'.
        return $this->hasOne(User::class, 'nik', 'N_NIK');
    }
}