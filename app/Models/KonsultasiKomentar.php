<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultasiKomentar extends Model
{
    protected $table = 't_konsultasi_komentar';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'ID_KONSULTASI',
        'N_NIK',
        'KOMENTAR',
        'PENGIRIM_ROLE',
        'CREATED_AT',
        'CREATED_BY',
        'UPDATED_BY',
        'UPDATED_AT'
    ];

    protected $casts = [
        'CREATED_AT' => 'datetime',
        'UPDATED_AT' => 'datetime'
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($komentar) {
            if (!$komentar->CREATED_AT) {
                $komentar->CREATED_AT = now();
            }
            if (!$komentar->PENGIRIM_ROLE) {
                $komentar->PENGIRIM_ROLE = 'USER';
            }
        });

        static::updating(function ($komentar) {
            $komentar->UPDATED_AT = now();
        });
    }

    /**
     * Relationship to Konsultasi
     */
    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'ID_KONSULTASI', 'ID');
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
     * Relationship to user who created the comment
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'nik');
    }

    /**
     * Relationship to user who updated the comment
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'nik');
    }

    /**
     * Scope for admin comments only
     */
    public function scopeAdminComments($query)
    {
        return $query->where('PENGIRIM_ROLE', 'ADMIN');
    }

    /**
     * Scope for user comments only
     */
    public function scopeUserComments($query)
    {
        return $query->where('PENGIRIM_ROLE', 'USER');
    }

    /**
     * Scope for comments by specific NIK
     */
    public function scopeByNik($query, $nik)
    {
        return $query->where('N_NIK', $nik);
    }

    /**
     * Scope for comments on specific konsultasi
     */
    public function scopeForKonsultasi($query, $konsultasiId)
    {
        return $query->where('ID_KONSULTASI', $konsultasiId);
    }

    /**
     * Get recent comments for a konsultasi
     */
    public static function getRecentForKonsultasi($konsultasiId, $limit = 5)
    {
        return static::forKonsultasi($konsultasiId)
                    ->with(['karyawan', 'user'])
                    ->orderBy('CREATED_AT', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get admin comments for a konsultasi
     */
    public static function getAdminCommentsForKonsultasi($konsultasiId)
    {
        return static::forKonsultasi($konsultasiId)
                    ->adminComments()
                    ->with(['karyawan', 'user'])
                    ->orderBy('CREATED_AT', 'asc')
                    ->get();
    }

    /**
     * Create a new comment
     */
    public static function createComment(array $data)
    {
        return static::create([
            'ID_KONSULTASI' => $data['konsultasi_id'],
            'N_NIK' => $data['nik'],
            'KOMENTAR' => $data['komentar'],
            'PENGIRIM_ROLE' => $data['role'] ?? 'USER',
            'CREATED_BY' => $data['created_by'] ?? $data['nik'],
            'CREATED_AT' => now()
        ]);
    }

    /**
     * Create admin comment
     */
    public static function createAdminComment($konsultasiId, $nik, $komentar)
    {
        return static::createComment([
            'konsultasi_id' => $konsultasiId,
            'nik' => $nik,
            'komentar' => $komentar,
            'role' => 'ADMIN',
            'created_by' => $nik
        ]);
    }

    /**
     * Create user comment
     */
    public static function createUserComment($konsultasiId, $nik, $komentar)
    {
        return static::createComment([
            'konsultasi_id' => $konsultasiId,
            'nik' => $nik,
            'komentar' => $komentar,
            'role' => 'USER',
            'created_by' => $nik
        ]);
    }

    /**
     * Check if comment is by admin
     */
    public function isAdminComment(): bool
    {
        return $this->PENGIRIM_ROLE === 'ADMIN';
    }

    /**
     * Check if comment is by user
     */
    public function isUserComment(): bool
    {
        return $this->PENGIRIM_ROLE === 'USER';
    }

    /**
     * Get human readable role
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->PENGIRIM_ROLE) {
            'ADMIN' => 'Admin SEKAR',
            'USER' => 'Anggota',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get comment author name
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->karyawan) {
            return $this->karyawan->V_NAMA_KARYAWAN;
        }
        
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->N_NIK;
    }

    /**
     * Get comment formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->CREATED_AT->format('d M Y, H:i');
    }

    /**
     * Get comment time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->CREATED_AT->diffForHumans();
    }

    /**
     * Alias for backward compatibility
     * Agar tetap bisa menggunakan JENIS_KOMENTAR di view
     */
    public function getJenisKomentarAttribute(): string
    {
        return $this->PENGIRIM_ROLE;
    }

    /**
     * Setter for backward compatibility
     */
    public function setJenisKomentarAttribute($value): void
    {
        $this->attributes['PENGIRIM_ROLE'] = $value;
    }
}