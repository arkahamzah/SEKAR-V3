<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekarJajaran extends Model
{
    protected $table = 't_sekar_jajaran';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'V_NAMA_KARYAWAN',
        'ID_JAJARAN',
        'START_DATE',
        'END_DATE',
        'CREATED_BY',
        'CREATED_AT',
        'IS_AKTIF'
    ];

    public function jajaran()
    {
        return $this->belongsTo(Jajaran::class, 'ID_JAJARAN', 'ID');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }
}