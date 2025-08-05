<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'membership_status',
        'membership_active_date',
        'is_gptp_preorder',
        'preorder_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'membership_active_date' => 'datetime',
            'is_gptp_preorder' => 'boolean',
        ];
    }

    /**
     * Relationship to Karyawan based on NIK
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'N_NIK');
    }

    /**
     * Relationship to SekarPengurus based on NIK
     */
    public function pengurus(): HasOne
    {
        return $this->hasOne(SekarPengurus::class, 'N_NIK', 'nik');
    }

    /**
     * Check if user is admin/pengurus
     */
    public function isAdmin(): bool
    {
        return $this->pengurus && $this->pengurus->role;
    }

    /**
     * Get user role name
     */
    public function getRoleName(): ?string
    {
        return $this->pengurus?->role?->NAME;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->pengurus?->role?->NAME === $roleName;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRole = $this->getRoleName();
        return $userRole && in_array($userRole, $roles);
    }

    // === GPTP PREORDER METHODS ===

    /**
     * Check if user is GPTP preorder member
     */
    public function isGPTPPreorder(): bool
    {
        return $this->is_gptp_preorder;
    }

    /**
     * Check if membership is currently active
     */
    public function isMembershipActive(): bool
    {
        if ($this->membership_status === 'inactive') {
            return false;
        }

        if ($this->membership_active_date) {
            return now()->gte($this->membership_active_date);
        }

        return $this->membership_status === 'active';
    }

    /**
     * Check if membership is pending (GPTP preorder)
     */
    public function isMembershipPending(): bool
    {
        return $this->membership_status === 'pending' || 
               ($this->membership_active_date && now()->lt($this->membership_active_date));
    }

    /**
     * Get days until membership becomes active
     */
    public function getDaysUntilActive(): int
    {
        if (!$this->membership_active_date || $this->isMembershipActive()) {
            return 0;
        }

        return now()->diffInDays($this->membership_active_date);
    }

    /**
     * Get formatted membership active date
     */
    public function getFormattedActiveDate(): string
    {
        if (!$this->membership_active_date) {
            return 'Langsung aktif';
        }

        return $this->membership_active_date->format('d F Y');
    }

    /**
     * Get membership status badge
     */
    public function getMembershipStatusBadge(): array
    {
        if ($this->isMembershipActive()) {
            return [
                'text' => 'Aktif',
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'check-circle'
            ];
        }

        if ($this->isMembershipPending()) {
            $daysLeft = $this->getDaysUntilActive();
            return [
                'text' => "Pending ({$daysLeft} hari)",
                'class' => 'bg-orange-100 text-orange-800',
                'icon' => 'clock'
            ];
        }

        return [
            'text' => 'Tidak Aktif',
            'class' => 'bg-red-100 text-red-800',
            'icon' => 'x-circle'
        ];
    }

    /**
     * Get GPTP preorder message
     */
    public function getGPTPMessage(): ?string
    {
        if (!$this->is_gptp_preorder) {
            return null;
        }

        if ($this->isMembershipActive()) {
            return 'Selamat! Membership GPTP Anda telah aktif.';
        }

        $daysLeft = $this->getDaysUntilActive();
        return "Sebagai karyawan GPTP, membership Anda akan aktif dalam {$daysLeft} hari ({$this->getFormattedActiveDate()}).";
    }

    /**
     * Get GPTP progress percentage (0-100)
     */
    public function getGPTPProgress(): int
    {
        if (!$this->is_gptp_preorder || !$this->membership_active_date) {
            return 100;
        }

        $createdDate = $this->created_at ?? now()->subYear();
        $totalDays = $createdDate->diffInDays($this->membership_active_date);
        $passedDays = $createdDate->diffInDays(now());
        
        if ($totalDays <= 0) {
            return 100;
        }

        $progress = min(100, max(0, ($passedDays / $totalDays) * 100));
        return (int) round($progress);
    }

    /**
     * Get remaining time until membership active (formatted)
     */
    public function getRemainingTimeFormatted(): string
    {
        if (!$this->membership_active_date || $this->isMembershipActive()) {
            return 'Aktif sekarang';
        }

        $now = now();
        $activeDate = $this->membership_active_date;
        
        $months = $now->diffInMonths($activeDate);
        $days = $now->diffInDays($activeDate) % 30;
        
        if ($months > 0) {
            return "{$months} bulan, {$days} hari";
        }
        
        return "{$days} hari";
    }

    /**
     * Activate GPTP membership (for admin use)
     */
    public function activateGPTPMembership(): bool
    {
        if (!$this->is_gptp_preorder) {
            return false;
        }

        $this->update([
            'membership_status' => 'active',
            'membership_active_date' => now(),
            'preorder_notes' => 'Activated manually by admin on ' . now()->format('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Check if user can access membership features
     */
    public function canAccessMembershipFeatures(): bool
    {
        return $this->isMembershipActive();
    }

    /**
     * Get membership restrictions for pending users
     */
    public function getMembershipRestrictions(): array
    {
        if ($this->isMembershipActive()) {
            return [];
        }

        return [
            'konsultasi' => 'Fitur konsultasi akan tersedia setelah membership aktif',
            'sertifikat' => 'Sertifikat akan tersedia setelah membership aktif',
            'voting' => 'Hak voting akan tersedia setelah membership aktif',
            'events' => 'Akses penuh ke event SEKAR akan tersedia setelah membership aktif'
        ];
    }

    /**
     * Relationship to Iuran
     */
    public function iuran(): HasOne
    {
        return $this->hasOne(Iuran::class, 'N_NIK', 'nik');
    }

    /**
     * Relationship to Konsultasi
     */
    public function konsultasi(): HasMany
    {
        return $this->hasMany(Konsultasi::class, 'N_NIK', 'nik');
    }

    /**
     * Scope for active members only
     */
    public function scopeActiveMembership($query)
    {
        return $query->where(function($q) {
            $q->where('membership_status', 'active')
              ->orWhere(function($subQuery) {
                  $subQuery->where('membership_status', 'pending')
                           ->where('membership_active_date', '<=', now());
              });
        });
    }

    /**
     * Scope for pending members (GPTP preorder)
     */
    public function scopePendingMembership($query)
    {
        return $query->where('membership_status', 'pending')
                     ->where('membership_active_date', '>', now());
    }

    /**
     * Scope for GPTP preorder members
     */
    public function scopeGPTPPreorder($query)
    {
        return $query->where('is_gptp_preorder', true);
    }

    /**
     * Scope for members who will be active soon (within 30 days)
     */
    public function scopeActivatingSoon($query)
    {
        return $query->where('membership_status', 'pending')
                     ->whereBetween('membership_active_date', [now(), now()->addDays(30)]);
    }

    /**
     * Scope for members by location
     */
    public function scopeByLocation($query, string $location)
    {
        return $query->whereHas('karyawan', function($q) use ($location) {
            $q->where('V_KOTA_GEDUNG', $location);
        });
    }

    /**
     * Get formatted user display name with position
     */
    public function getDisplayNameAttribute(): string
    {
        $karyawan = $this->karyawan;
        if ($karyawan) {
            return $karyawan->V_NAMA_KARYAWAN . ' (' . $karyawan->V_SHORT_POSISI . ')';
        }
        return $this->name;
    }

    /**
     * Get user's location from karyawan data
     */
    public function getLocationAttribute(): ?string
    {
        return $this->karyawan?->V_KOTA_GEDUNG;
    }

    /**
     * Get user's full position info
     */
    public function getFullPositionAttribute(): string
    {
        $karyawan = $this->karyawan;
        if (!$karyawan) {
            return 'N/A';
        }

        $parts = array_filter([
            $karyawan->V_SHORT_POSISI,
            $karyawan->V_SHORT_UNIT,
            $karyawan->V_SHORT_DIVISI
        ]);

        return implode(' - ', $parts);
    }

    /**
     * Get user's DPW (for pengurus)
     */
    public function getDPW(): ?string
    {
        return $this->pengurus?->DPW;
    }

    /**
     * Get user's DPD (for pengurus)
     */
    public function getDPD(): ?string
    {
        return $this->pengurus?->DPD;
    }

    /**
     * Get user's DPP (for pengurus)
     */
    public function getDPP(): ?string
    {
        return $this->pengurus?->DPP;
    }

    /**
     * Check if user is from specific location
     */
    public function isFromLocation(string $location): bool
    {
        return $this->karyawan?->V_KOTA_GEDUNG === $location;
    }

    /**
     * Scope for filtering by role
     */
    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('pengurus.role', function($q) use ($roleName) {
            $q->where('NAME', $roleName);
        });
    }

    /**
     * Scope for admin users only
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas('pengurus.role');
    }

    /**
     * Scope for regular users (non-admin)
     */
    public function scopeRegularUsers($query)
    {
        return $query->whereDoesntHave('pengurus.role');
    }

    /**
     * Scope for users by divisi
     */
    public function scopeByDivisi($query, string $divisi)
    {
        return $query->whereHas('karyawan', function($q) use ($divisi) {
            $q->where('V_SHORT_DIVISI', 'LIKE', "%{$divisi}%");
        });
    }

    /**
     * Get all pending GPTP members
     */
    public static function getPendingGPTPMembers()
    {
        return static::gptpPreorder()
                    ->pendingMembership()
                    ->with(['karyawan'])
                    ->orderBy('membership_active_date')
                    ->get();
    }

    /**
     * Get members activating soon
     */
    public static function getMembersActivatingSoon()
    {
        return static::activatingSoon()
                    ->with(['karyawan'])
                    ->orderBy('membership_active_date')
                    ->get();
    }

    /**
     * Get membership statistics
     */
    public static function getMembershipStats(): array
    {
        return [
            'total_members' => static::count(),
            'active_members' => static::activeMembership()->count(),
            'pending_members' => static::pendingMembership()->count(),
            'gptp_preorder' => static::gptpPreorder()->count(),
            'activating_soon' => static::activatingSoon()->count(),
        ];
    }
}