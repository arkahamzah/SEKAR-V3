<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IuranBulanan extends Model
{
    protected $table = 't_iuran_bulanan';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'TAHUN',
        'BULAN',
        'IURAN_WAJIB',
        'IURAN_SUKARELA',
        'TOTAL_IURAN',
        'STATUS',
        'TGL_BAYAR',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT'
    ];

    protected $casts = [
        'TGL_BAYAR' => 'datetime',
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }

    public function getStatusTextAttribute()
    {
        switch ($this->STATUS) {
            case 'LUNAS':
                return 'Lunas';
            case 'BELUM_BAYAR':
                return 'Belum Bayar';
            case 'TERLAMBAT':
                return 'Terlambat';
            default:
                return 'Unknown';
        }
    }

    public function getStatusColorAttribute()
    {
        switch ($this->STATUS) {
            case 'LUNAS':
                return 'green';
            case 'BELUM_BAYAR':
                return 'yellow';
            case 'TERLAMBAT':
                return 'red';
            default:
                return 'gray';
        }
    }

    public function getStatusIconAttribute()
    {
        switch ($this->STATUS) {
            case 'LUNAS':
                return 'check-circle';
            case 'BELUM_BAYAR':
                return 'clock';
            case 'TERLAMBAT':
                return 'exclamation-triangle';
            default:
                return 'question-mark-circle';
        }
    }

    // Check if this payment is overdue
    public function getIsOverdueAttribute()
    {
        if ($this->STATUS === 'LUNAS') {
            return false;
        }

        $dueDate = Carbon::createFromDate($this->TAHUN, $this->BULAN, 15); // Due on 15th of each month
        return Carbon::now()->isAfter($dueDate);
    }

    // Get formatted month name in Indonesian
    public function getBulanNamaAttribute()
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $bulan = str_pad($this->BULAN, 2, '0', STR_PAD_LEFT);
        return isset($months[$bulan]) ? $months[$bulan] : 'Unknown';
    }

    // Get period display (e.g., "Januari 2025")
    public function getPeriodeAttribute()
    {
        return $this->bulan_nama . ' ' . $this->TAHUN;
    }

    // Scope for getting only paid records
    public function scopePaid($query)
    {
        return $query->where('STATUS', 'LUNAS');
    }

    // Scope for getting unpaid records
    public function scopeUnpaid($query)
    {
        return $query->whereIn('STATUS', ['BELUM_BAYAR', 'TERLAMBAT']);
    }

    // Scope for getting overdue records
    public function scopeOverdue($query)
    {
        return $query->where('STATUS', 'TERLAMBAT');
    }

    // Scope for getting records by year
    public function scopeByYear($query, $year)
    {
        return $query->where('TAHUN', $year);
    }

    // Scope for getting records by month
    public function scopeByMonth($query, $month)
    {
        return $query->where('BULAN', str_pad($month, 2, '0', STR_PAD_LEFT));
    }

    // Scope for ordering by period (year and month)
    public function scopeOrderByPeriod($query, $direction = 'desc')
    {
        return $query->orderBy('TAHUN', $direction)
                    ->orderBy('BULAN', $direction);
    }

    // Static method to create monthly payment record - AUTO PAID from payroll
    public static function createMonthlyPayment($nik, $year, $month, $iuranWajib, $iuranSukarela, $createdBy = 'PAYROLL_SYSTEM')
    {
        $totalIuran = $iuranWajib + $iuranSukarela;
        $paymentDate = Carbon::createFromDate($year, $month, 1);

        return self::create([
            'N_NIK' => $nik,
            'TAHUN' => $year,
            'BULAN' => str_pad($month, 2, '0', STR_PAD_LEFT),
            'IURAN_WAJIB' => $iuranWajib,
            'IURAN_SUKARELA' => $iuranSukarela,
            'TOTAL_IURAN' => $totalIuran,
            'STATUS' => 'LUNAS', // AUTO LUNAS dari payroll
            'TGL_BAYAR' => $paymentDate,
            'CREATED_BY' => $createdBy,
            'CREATED_AT' => now()
        ]);
    }

    // Mark payment as paid
    public function markAsPaid($paidBy = 'SYSTEM')
    {
        return $this->update([
            'STATUS' => 'LUNAS',
            'TGL_BAYAR' => now(),
            'UPDATED_BY' => $paidBy,
            'UPDATED_AT' => now()
        ]);
    }

    // Mark payment as overdue
    public function markAsOverdue($updatedBy = 'SYSTEM')
    {
        return $this->update([
            'STATUS' => 'TERLAMBAT',
            'UPDATED_BY' => $updatedBy,
            'UPDATED_AT' => now()
        ]);
    }
}