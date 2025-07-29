<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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

        // Clear cache when konsultasi is modified
        static::saved(function ($konsultasi) {
            Cache::forget('konsultasi_stats');
            Cache::forget('konsultasi_stats_user_' . $konsultasi->N_NIK);
        });

        static::deleted(function ($konsultasi) {
            Cache::forget('konsultasi_stats');
            Cache::forget('konsultasi_stats_user_' . $konsultasi->N_NIK);
        });
    }

    /**
     * Relationship to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'N_NIK', 'nik');
    }

    /**
     * Relationship to Karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'N_NIK', 'N_NIK');
    }

    /**
     * Relationship to Comments (menggunakan nama tabel yang benar)
     */
    public function komentar(): HasMany
    {
        return $this->hasMany(KonsultasiKomentar::class, 'ID_KONSULTASI', 'ID')
                    ->orderBy('CREATED_AT', 'asc');
    }

    /**
     * Relationship to latest comment
     */
    public function latestComment(): HasMany
    {
        return $this->hasMany(KonsultasiKomentar::class, 'ID_KONSULTASI', 'ID')
                    ->orderBy('CREATED_AT', 'desc')
                    ->limit(1);
    }

    /**
     * Relationship to admin comments only
     */
    public function adminComments(): HasMany
    {
        return $this->hasMany(KonsultasiKomentar::class, 'ID_KONSULTASI', 'ID')
                    ->where('PENGIRIM_ROLE', 'ADMIN')
                    ->orderBy('CREATED_AT', 'desc');
    }

    /**
     * Relationship to user who closed the konsultasi
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CLOSED_BY', 'nik');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('STATUS', $status);
    }

    /**
     * Scope for filtering by jenis
     */
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('JENIS', $jenis);
    }

    /**
     * Scope for filtering by tujuan
     */
    public function scopeByTujuan($query, $tujuan)
    {
        return $query->where('TUJUAN', $tujuan);
    }

    /**
     * Scope for filtering by NIK
     */
    public function scopeByNik($query, $nik)
    {
        return $query->where('N_NIK', $nik);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $keywords)
    {
        return $query->where(function ($q) use ($keywords) {
            $q->where('JUDUL', 'LIKE', "%{$keywords}%")
              ->orWhere('DESKRIPSI', 'LIKE', "%{$keywords}%")
              ->orWhere('KATEGORI_ADVOKASI', 'LIKE', "%{$keywords}%");
        });
    }

    /**
     * Scope for admin access based on target
     */
    public function scopeForAdminLevel($query, $adminLevel)
    {
        switch($adminLevel) {
            case 4: // ADM - can see all
                break;
            case 3: // ADMIN_DPP - can see DPP and GENERAL
                $query->whereIn('TUJUAN', ['DPP', 'GENERAL']);
                break;
            case 2: // ADMIN_DPW - can see DPW, DPP, and GENERAL
                $query->whereIn('TUJUAN', ['DPW', 'DPP', 'GENERAL']);
                break;
            case 1: // ADMIN_DPD - can see DPD, DPW, DPP, and GENERAL
                $query->whereIn('TUJUAN', ['DPD', 'DPW', 'DPP', 'GENERAL']);
                break;
            default:
                // No admin access, return empty
                $query->where('ID', 0);
        }
        
        return $query;
    }

    /**
     * Get open konsultasi count for user
     */
    public static function getOpenCountForUser(string $nik): int
    {
        return static::where('N_NIK', $nik)
                    ->where('STATUS', 'OPEN')
                    ->count();
    }

    /**
     * Get all konsultasi for admin with filters
     */
    public static function getForAdmin(int $adminLevel, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::with(['karyawan', 'komentar'])
                      ->forAdminLevel($adminLevel);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['jenis'])) {
            $query->byJenis($filters['jenis']);
        }

        if (!empty($filters['tujuan'])) {
            $query->byTujuan($filters['tujuan']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->orderBy('CREATED_AT', 'desc');
    }

    /**
     * Get konsultasi statistics
     */
    public static function getStats(string $nik = null): array
    {
        $cacheKey = $nik ? "konsultasi_stats_user_{$nik}" : 'konsultasi_stats';
        
        return Cache::remember($cacheKey, 300, function () use ($nik) {
            $query = static::query();
            
            if ($nik) {
                $query->where('N_NIK', $nik);
            }
            
            $stats = [
                'total' => $query->count(),
                'open' => (clone $query)->where('STATUS', 'OPEN')->count(),
                'in_progress' => (clone $query)->where('STATUS', 'IN_PROGRESS')->count(),
                'closed' => (clone $query)->where('STATUS', 'CLOSED')->count(),
                'resolved' => (clone $query)->where('STATUS', 'RESOLVED')->count(),
                'advokasi' => (clone $query)->where('JENIS', 'ADVOKASI')->count(),
                'aspirasi' => (clone $query)->where('JENIS', 'ASPIRASI')->count(),
            ];
            
            $stats['pending'] = $stats['open'] + $stats['in_progress'];
            
            return $stats;
        });
    }

    /**
     * Get recent konsultasi for user
     */
    public static function getRecentForUser(string $nik, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('N_NIK', $nik)
                    ->with(['karyawan', 'komentar'])
                    ->orderBy('CREATED_AT', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get all konsultasi for admin dashboard
     */
    public static function getAllForAdmin(int $adminLevel, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with(['karyawan', 'komentar'])
                    ->forAdminLevel($adminLevel)
                    ->orderBy('CREATED_AT', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Search konsultasi by keywords
     */
    public static function searchKonsultasi(string $keywords, string $nik = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::search($keywords);

        if ($nik) {
            $query->byNik($nik);
        }

        return $query->with(['karyawan', 'komentar'])
                    ->orderBy('CREATED_AT', 'desc')
                    ->get();
    }

    /**
     * Close konsultasi
     */
    public function close(string $closedBy): bool
    {
        return $this->update([
            'STATUS' => 'CLOSED',
            'CLOSED_BY' => $closedBy,
            'CLOSED_AT' => now(),
            'UPDATED_BY' => $closedBy,
            'UPDATED_AT' => now()
        ]);
    }

    /**
     * Escalate konsultasi to higher level
     */
    public function escalate(string $newTarget, string $escalatedBy): bool
    {
        return $this->update([
            'TUJUAN' => $newTarget,
            'STATUS' => 'OPEN', // Reset to open for higher level
            'UPDATED_BY' => $escalatedBy,
            'UPDATED_AT' => now()
        ]);
    }

    /**
     * Mark as in progress
     */
    public function markInProgress(string $updatedBy): bool
    {
        if ($this->STATUS === 'OPEN') {
            return $this->update([
                'STATUS' => 'IN_PROGRESS',
                'UPDATED_BY' => $updatedBy,
                'UPDATED_AT' => now()
            ]);
        }
        return false;
    }

    /**
     * Check if konsultasi can be escalated
     */
    public function canBeEscalated(): bool
    {
        return $this->STATUS !== 'CLOSED' && $this->TUJUAN !== 'GENERAL';
    }

    /**
     * Get available escalation targets
     */
    public function getEscalationTargets(): array
    {
        switch($this->TUJUAN) {
            case 'DPD':
                return [
                    'DPW' => 'DPW (Dewan Pengurus Wilayah)',
                    'DPP' => 'DPP (Dewan Pengurus Pusat)',
                    'GENERAL' => 'SEKAR Pusat'
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
        
        return $labels[$this->TUJUAN] ?? $this->TUJUAN;
    }
}