<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\User; // Ditambahkan untuk relasi follower

class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 't_konsultasi';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'N_NIK',
        'JENIS',
        'KATEGORI_ADVOKASI',
        'TUJUAN',
        'TUJUAN_SPESIFIK',
        'JUDUL',
        'DESKRIPSI',
        'STATUS',
        'CREATED_BY',
        'CREATED_AT',
        'UPDATED_BY',
        'UPDATED_AT',
        'CLOSED_BY',
        'CLOSED_AT'
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime',
        'CLOSED_AT' => 'datetime',
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($konsultasi) {
            if (!$konsultasi->CREATED_AT) {
                $konsultasi->CREATED_AT = now();
            }
            if (!$konsultasi->STATUS) {
                $konsultasi->STATUS = 'OPEN';
            }
        });

        static::updating(function ($konsultasi) {
            $konsultasi->UPDATED_AT = now();
        });
    }

    /**
     * Relationship to Karyawan (submitter)
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }

    /**
     * Relationship to KonsultasiKomentar
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(KonsultasiKomentar::class, 'ID_KONSULTASI', 'ID');
    }

    /**
     * [BARU] Relationship to KonsultasiFollower
     * Mendapatkan daftar user yang mengikuti konsultasi ini.
     */
    public function followers(): HasMany
    {
        return $this->hasMany(KonsultasiFollower::class, 'konsultasi_id', 'ID');
    }

    /**
     * [BARU] Helper function to check if a user is a follower.
     *
     * @param User $user
     * @return boolean
     */
    public function isFollowedBy(User $user): bool
    {
        // Cek apakah ada record follower dengan user_nik yang sesuai
        return $this->followers()->where('user_nik', $user->nik)->exists();
    }


    /**
     * Get statistics for konsultasi
     */
    public static function getStats($nik = null): array
    {
        return Cache::remember('konsultasi_stats_' . ($nik ?? 'all'), 60, function () use ($nik) {
            $query = self::query();

            if ($nik) {
                $query->where('N_NIK', $nik);
            }

            $total = (clone $query)->count();
            $open = (clone $query)->where('STATUS', 'OPEN')->count();
            $in_progress = (clone $query)->where('STATUS', 'IN_PROGRESS')->count();
            $closed = (clone $query)->where('STATUS', 'CLOSED')->count();

            return compact('total', 'open', 'in_progress', 'closed');
        });
    }

    /**
     * Get valid escalation targets based on current level
     */
    public static function getValidEscalationTargets(string $currentLevel): array
    {
        switch($currentLevel) {
            case 'DPD':
                return [
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)'
                ];
            case 'DPW':
                return [
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
                ];
            case 'DPP':
                return [
                    'GENERAL' => 'SEKAR Pusat'
                ];
            case 'GENERAL':
                return [];
            default:
                return [
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
                ];
        }
    }

    /**
     * Get human readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->STATUS) {
            'OPEN' => 'Terbuka',
            'IN_PROGRESS' => 'Sedang Diproses',
            'CLOSED' => 'Ditutup',
            'RESOLVED' => 'Selesai',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get human readable jenis
     */
    public function getJenisLabelAttribute(): string
    {
        return match($this->JENIS) {
            'ADVOKASI' => 'Advokasi',
            'ASPIRASI' => 'Aspirasi',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get human readable tujuan
     */
    public function getTujuanLabelAttribute(): string
    {
        $labels = [
            'DPD' => 'DPD (Dewan Pengurus Daerah)',
            'DPW' => 'DPW (Dewan Pengurus Wilayah)',
            'DPP' => 'DPP (Dewan Pengurus Pusat)',
            'GENERAL' => 'SEKAR Pusat'
        ];

        $label = $labels[$this->TUJUAN] ?? $this->TUJUAN;

        if ($this->TUJUAN_SPESIFIK && $this->TUJUAN !== 'DPP' && $this->TUJUAN !== 'GENERAL') {
            $label .= " ({$this->TUJUAN_SPESIFIK})";
        }

        return $label;
    }
}