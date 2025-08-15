<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultasiFollower extends Model
{
    use HasFactory;

    protected $table = 'konsultasi_followers';

    protected $fillable = [
        'konsultasi_id',
        'user_nik',
    ];

    /**
     * Get the user associated with the follower entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_nik', 'nik');
    }

    /**
     * Get the consultation associated with the follower entry.
     */
    public function konsultasi(): BelongsTo
    {
        return $this->belongsTo(Konsultasi::class, 'konsultasi_id', 'ID');
    }
}