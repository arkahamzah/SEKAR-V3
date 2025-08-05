<?php

// File: app/Models/Params.php

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
        'TAHUN',
        'IS_AKTIF',
        'CREATED_BY',
        'CREATED_AT'
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime'
    ];

    // Get active params for a specific year
    public static function getActiveParams($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        return self::where('IS_AKTIF', '1')
                  ->where('TAHUN', $year)
                  ->first();
    }

    // Get current iuran wajib
    public static function getCurrentIuranWajib()
    {
        $params = self::getActiveParams();
        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : 25000; // Default fallback
    }

    // Get current banpers nominal
    public static function getCurrentBanpers()
    {
        $params = self::getActiveParams();
        return $params ? (int)$params->NOMINAL_BANPERS : 20000; // Default fallback
    }

    // Check if params exist for year
    public static function hasParamsForYear($year)
    {
        return self::where('TAHUN', $year)
                  ->where('IS_AKTIF', '1')
                  ->exists();
    }

    // Create or update params for year
    public static function setParamsForYear($year, $iuranWajib, $banpers, $createdBy = 'SYSTEM')
    {
        // Deactivate existing params for the year
        self::where('TAHUN', $year)->update(['IS_AKTIF' => '0']);

        // Create new active params
        return self::create([
            'NOMINAL_IURAN_WAJIB' => $iuranWajib,
            'NOMINAL_BANPERS' => $banpers,
            'TAHUN' => $year,
            'IS_AKTIF' => '1',
            'CREATED_BY' => $createdBy,
            'CREATED_AT' => now()
        ]);
    }
}