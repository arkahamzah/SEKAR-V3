<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            if (!$token->created_at) {
                $token->created_at = now();
            }
        });
    }

    /**
     * Relationship with User via email
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Check if token is expired (1 hour)
     */
    public function isExpired(): bool
    {
        return $this->created_at->addHour()->isPast();
    }

    /**
     * Check if token is still valid
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get remaining time in minutes
     */
    public function getRemainingTimeAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->created_at->addHour()->diffInMinutes(now());
    }

    /**
     * Get human readable expiry time
     */
    public function getExpiryTimeAttribute(): string
    {
        return $this->created_at->addHour()->format('d M Y, H:i') . ' WIB';
    }

    /**
     * Scope for valid tokens only
     */
    public function scopeValid($query)
    {
        return $query->where('created_at', '>', now()->subHour());
    }

    /**
     * Scope for expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('created_at', '<=', now()->subHour());
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpired(): int
    {
        return static::expired()->delete();
    }

    /**
     * Find valid token by email and token string
     */
    public static function findValidToken(string $email, string $token): ?self
    {
        $records = static::where('email', $email)->valid()->get();

        foreach ($records as $record) {
            if (\Hash::check($token, $record->token)) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Create new reset token for user
     */
    public static function createForUser(User $user): string
    {
        // Delete existing tokens
        static::where('email', $user->email)->delete();

        // Generate new token
        $token = \Str::random(64);

        // Create record
        static::create([
            'email' => $user->email,
            'token' => \Hash::make($token),
            'created_at' => now()
        ]);

        return $token;
    }

    /**
     * Get token statistics
     */
    public static function getStats(): array
    {
        return [
            'total' => static::count(),
            'valid' => static::valid()->count(),
            'expired' => static::expired()->count(),
            'today' => static::whereDate('created_at', today())->count()
        ];
    }
}
