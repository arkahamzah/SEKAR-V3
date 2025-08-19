<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatSignature extends Model
{
    use HasFactory;

    protected $table = 't_sertifikat_signatures';

    protected $fillable = [
        'nama_pejabat',
        'jabatan',
        'signature_file',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}