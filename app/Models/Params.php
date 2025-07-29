<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Params extends Model
{
    protected $table = 'p_params';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'NOMINAL_IURAN_WAJIB',
        'NOMINAL_BANPERS',
        'CREATED_BY',
        'CREATED_AT',
        'TAHUN',
        'IS_AKTIF'
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime'
    ];
}