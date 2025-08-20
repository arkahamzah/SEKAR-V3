<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewKaryawan extends Model
{
    use HasFactory;

    /**
     * Nama view di database yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'v_karyawan_mapping';

    /**
     * Primary key dari view.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Menandakan bahwa model ini tidak menggunakan timestamps (created_at & updated_at).
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ID',
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
        'PSA_Kodlok',
        'DPD',
        'DPW',
    ];
}