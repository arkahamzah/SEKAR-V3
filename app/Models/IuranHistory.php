<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IuranHistory extends Model
{
    protected $table = 't_iuran_history';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'JENIS',
        'NOMINAL_LAMA',
        'NOMINAL_BARU',
        'STATUS_PROSES',
        'TGL_PERUBAHAN',
        'TGL_PROSES',
        'TGL_IMPLEMENTASI',
        'KETERANGAN',
        'CREATED_BY',
        'CREATED_AT'
    ];

    protected $casts = [
        'TGL_PERUBAHAN' => 'datetime',
        'TGL_PROSES' => 'datetime',
        'TGL_IMPLEMENTASI' => 'datetime',
        'CREATED_AT' => 'datetime'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }

    public function getStatusTextAttribute()
    {
        return match($this->STATUS_PROSES) {
            'PENDING' => 'Menunggu Proses',
            'PROCESSED' => 'Sedang Diproses HC',
            'IMPLEMENTED' => 'Sudah Diterapkan',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->STATUS_PROSES) {
            'PENDING' => 'yellow',
            'PROCESSED' => 'blue',
            'IMPLEMENTED' => 'green',
            default => 'gray'
        };
    }

    // Auto-calculate process and implementation dates
    public static function createWithDates($data)
    {
        $tglPerubahan = Carbon::parse($data['TGL_PERUBAHAN']);
        
        // Calculate process date (n+1 month, on 20th)
        $tglProses = $tglPerubahan->copy()->addMonth()->day(20);
        
        // Calculate implementation date (n+2 months, on 1st)
        $tglImplementasi = $tglPerubahan->copy()->addMonths(2)->day(1);
        
        $data['TGL_PROSES'] = $tglProses;
        $data['TGL_IMPLEMENTASI'] = $tglImplementasi;
        
        return self::create($data);
    }
}