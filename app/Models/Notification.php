<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'data',
        'notifiable_type',
        'notifiable_id',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    /**
     * Get the notifiable entity (User)
     */
    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id', 'nik');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Get notification message - SAFE VERSION
     */
    public function getMessage(): string
    {
        $data = $this->data ?? [];
        
        // Ensure required fields exist
        $jenis = $data['jenis'] ?? 'konsultasi';
        $judul = $data['judul'] ?? 'Konsultasi';
        
        try {
            return match($this->type) {
                'comment' => $this->getCommentMessage($data, $jenis, $judul),
                'escalate' => $this->getEscalateMessage($data, $jenis, $judul),
                'closed' => $this->getClosedMessage($data, $jenis, $judul),
                'new' => $this->getNewMessage($data, $jenis, $judul),
                default => 'Ada notifikasi baru'
            };
        } catch (\Exception $e) {
            \Log::error('Error generating notification message', [
                'notification_id' => $this->id,
                'type' => $this->type,
                'data' => $this->data,
                'error' => $e->getMessage()
            ]);
            return 'Ada notifikasi baru';
        }
    }

    /**
     * Get comment notification message
     */
    private function getCommentMessage(array $data, string $jenis, string $judul): string
    {
        if (isset($data['admin_level'])) {
            $adminLevel = $data['admin_level'];
            return "Ada balasan baru pada {$jenis} \"{$judul}\" dari {$adminLevel}";
        } else {
            $commentBy = $data['comment_by'] ?? 'seseorang';
            return "Ada komentar baru dari {$commentBy} untuk {$jenis} \"{$judul}\"";
        }
    }

    /**
     * Get escalate notification message
     */
    private function getEscalateMessage(array $data, string $jenis, string $judul): string
    {
        $escalateTo = $data['escalate_to'] ?? 'level yang lebih tinggi';
        return "{$jenis} \"{$judul}\" telah dieskalasi ke {$escalateTo}";
    }

    /**
     * Get closed notification message
     */
    private function getClosedMessage(array $data, string $jenis, string $judul): string
    {
        $closedBy = $data['closed_by_level'] ?? 'admin';
        return "{$jenis} \"{$judul}\" telah diselesaikan dan ditutup oleh {$closedBy}";
    }

    /**
     * Get new notification message
     */
    private function getNewMessage(array $data, string $jenis, string $judul): string
    {
        $fromUser = $data['from_user'] ?? 'seseorang';
        if (isset($data['escalated']) && $data['escalated']) {
            return "Ada {$jenis} tereskalasi: \"{$judul}\" yang perlu ditindaklanjuti";
        }
        return "Ada {$jenis} baru dari {$fromUser}: \"{$judul}\" yang perlu ditindaklanjuti";
    }

    /**
     * Get notification icon
     */
    public function getIcon(): string
    {
        return match($this->type) {
            'comment' => 'chat-bubble-left-right',
            'escalate' => 'arrow-trending-up',
            'closed' => 'check-circle',
            'new' => 'bell',
            default => 'bell'
        };
    }

    /**
     * Get notification color
     */
    public function getColor(): string
    {
        return match($this->type) {
            'comment' => 'blue',
            'escalate' => 'orange',
            'closed' => 'green',
            'new' => 'purple',
            default => 'gray'
        };
    }
}