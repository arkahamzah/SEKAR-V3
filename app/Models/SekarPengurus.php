<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekarPengurus extends Model
{
    protected $table = 't_sekar_pengurus';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'V_SHORT_POSISI',
        'V_SHORT_UNIT',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT',
        'DPP',
        'DPW',
        'DPD',
        'ID_ROLES'
    ];

    public function role()
    {
        return $this->belongsTo(SekarRole::class, 'ID_ROLES', 'ID');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }
}
