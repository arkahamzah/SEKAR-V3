<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExAnggota extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_ex_anggota';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

    /**
     * Indicates if the model should be timestamped.
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
        'N_NIK',
        'V_NAMA_KARYAWAN',
        'V_SHORT_POSISI',
        'V_SHORT_DIVISI',
        'NO_TELP',
        'TGL_KELUAR',
        'ALASAN_KELUAR',
        'IURAN_WAJIB_TERAKHIR',
        'IURAN_SUKARELA_TERAKHIR',
        'DPP',
        'DPW',
        'DPD',
        'V_KOTA_GEDUNG',
        'CREATED_BY',
        'CREATED_AT',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'TGL_KELUAR' => 'datetime',
        'CREATED_AT' => 'datetime',
        'IURAN_WAJIB_TERAKHIR' => 'decimal:2',
        'IURAN_SUKARELA_TERAKHIR' => 'decimal:2',
    ];

    /**
     * Get the formatted tanggal keluar attribute.
     *
     * @return string|null
     */
    public function getFormattedTanggalKeluarAttribute(): ?string
    {
        return $this->TGL_KELUAR ? $this->TGL_KELUAR->format('d-m-Y') : null;
    }

    /**
     * Get the formatted iuran wajib terakhir attribute.
     *
     * @return string
     */
    public function getFormattedIuranWajibTerakhirAttribute(): string
    {
        return $this->IURAN_WAJIB_TERAKHIR 
            ? 'Rp ' . number_format($this->IURAN_WAJIB_TERAKHIR, 0, ',', '.') 
            : 'Rp 0';
    }

    /**
     * Get the formatted iuran sukarela terakhir attribute.
     *
     * @return string
     */
    public function getFormattedIuranSukarelaTerakhirAttribute(): string
    {
        return $this->IURAN_SUKARELA_TERAKHIR 
            ? 'Rp ' . number_format($this->IURAN_SUKARELA_TERAKHIR, 0, ',', '.') 
            : 'Rp 0';
    }

    /**
     * Scope a query to search by name or NIK.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('V_NAMA_KARYAWAN', 'LIKE', "%{$search}%")
              ->orWhere('N_NIK', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by DPW.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $dpw
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDpw($query, $dpw)
    {
        return $query->where('DPW', $dpw);
    }

    /**
     * Scope a query to filter by DPD.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $dpd
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDpd($query, $dpd)
    {
        return $query->where('DPD', $dpd);
    }

    /**
     * Get ex anggota by recent departure.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecentDeparture($query, $days = 30)
    {
        return $query->where('TGL_KELUAR', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Get ex anggota statistics by DPW.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getStatisticsByDpw()
    {
        return self::selectRaw('DPW, COUNT(*) as total, 
                               AVG(IURAN_WAJIB_TERAKHIR) as avg_iuran_wajib,
                               AVG(IURAN_SUKARELA_TERAKHIR) as avg_iuran_sukarela')
                   ->whereNotNull('DPW')
                   ->where('DPW', '!=', '')
                   ->groupBy('DPW')
                   ->orderBy('total', 'desc')
                   ->get();
    }

    /**
     * Get monthly departure statistics.
     *
     * @param  int  $year
     * @return \Illuminate\Support\Collection
     */
    public static function getMonthlyDepartureStats($year = null)
    {
        $year = $year ?: Carbon::now()->year;
        
        return self::selectRaw('MONTH(TGL_KELUAR) as month, COUNT(*) as total')
                   ->whereYear('TGL_KELUAR', $year)
                   ->groupBy('month')
                   ->orderBy('month')
                   ->get();
    }
}