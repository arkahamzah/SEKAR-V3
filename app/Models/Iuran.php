<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    protected $table = 't_iuran';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'IURAN_WAJIB',
        'IURAN_SUKARELA',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATE_BY',
        'UPDATED_AT'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }
}