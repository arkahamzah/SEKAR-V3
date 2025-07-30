<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create notification for new konsultasi (to admin)
     */
    public function notifyNewKonsultasi(Konsultasi $konsultasi): void
    {
        try {
            // Get admin users based on konsultasi target
            $adminUsers = $this->getAdminUsersByTarget($konsultasi->TUJUAN);
            
            Log::info('Found admin users for notification', [
                'konsultasi_id' => $konsultasi->ID,
                'target' => $konsultasi->TUJUAN,
                'admin_count' => count($adminUsers)
            ]);
            
            foreach ($adminUsers as $admin) {
                $notification = Notification::create([
                    'type' => 'new',
                    'data' => [
                        'konsultasi_id' => $konsultasi->ID,
                        'jenis' => strtolower($konsultasi->JENIS),
                        'judul' => $konsultasi->JUDUL,
                        'from_user' => $konsultasi->karyawan->V_NAMA_KARYAWAN ?? $konsultasi->N_NIK,
                        'target_level' => $konsultasi->TUJUAN
                    ],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $admin->nik
                ]);
                
                Log::info('Notification created successfully', [
                    'notification_id' => $notification->id,
                    'admin_nik' => $admin->nik,
                    'konsultasi_id' => $konsultasi->ID
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create new konsultasi notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create notification for new comment
     */
    public function notifyNewComment(Konsultasi $konsultasi, $commentBy, $isAdminComment = false): void
    {
        try {
            // Safe way to get commenter name
            $commenterName = $this->getCommenterName($commentBy);
            
            if ($isAdminComment) {
                // Notify original user about admin response
                $user = User::where('nik', $konsultasi->N_NIK)->first();
                if ($user) {
                    Notification::create([
                        'type' => 'comment',
                        'data' => [
                            'konsultasi_id' => $konsultasi->ID,
                            'jenis' => strtolower($konsultasi->JENIS),
                            'judul' => $konsultasi->JUDUL,
                            'admin_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN),
                            'comment_by' => $commenterName
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $user->nik
                    ]);
                }
            } else {
                // Notify admin about user response
                $adminUsers = $this->getAdminUsersByTarget($konsultasi->TUJUAN);
                
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'type' => 'comment',
                        'data' => [
                            'konsultasi_id' => $konsultasi->ID,
                            'jenis' => strtolower($konsultasi->JENIS),
                            'judul' => $konsultasi->JUDUL,
                            'admin_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN),
                            'comment_by' => $commenterName
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $admin->nik
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create comment notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create notification for escalation
     */
    public function notifyEscalation(Konsultasi $konsultasi, $escalateTo): void
    {
        try {
            // Notify original user about escalation
            $user = User::where('nik', $konsultasi->N_NIK)->first();
            if ($user) {
                Notification::create([
                    'type' => 'escalate',
                    'data' => [
                        'konsultasi_id' => $konsultasi->ID,
                        'jenis' => strtolower($konsultasi->JENIS),
                        'judul' => $konsultasi->JUDUL,
                        'escalate_from' => $this->getAdminLevelByTarget($konsultasi->TUJUAN),
                        'escalate_to' => $this->getAdminLevelByTarget($escalateTo)
                    ],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->nik
                ]);
            }

            // Notify new admin level
            $newAdminUsers = $this->getAdminUsersByTarget($escalateTo);
            foreach ($newAdminUsers as $admin) {
                Notification::create([
                    'type' => 'new',
                    'data' => [
                        'konsultasi_id' => $konsultasi->ID,
                        'jenis' => strtolower($konsultasi->JENIS),
                        'judul' => $konsultasi->JUDUL,
                        'from_user' => $konsultasi->karyawan->V_NAMA_KARYAWAN ?? $konsultasi->N_NIK,
                        'target_level' => $escalateTo,
                        'escalated' => true
                    ],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $admin->nik
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create escalation notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create notification for closed konsultasi
     */
    public function notifyKonsultasiClosed(Konsultasi $konsultasi): void
    {
        try {
            $user = User::where('nik', $konsultasi->N_NIK)->first();
            if ($user) {
                Notification::create([
                    'type' => 'closed',
                    'data' => [
                        'konsultasi_id' => $konsultasi->ID,
                        'jenis' => strtolower($konsultasi->JENIS),
                        'judul' => $konsultasi->JUDUL,
                        'closed_by_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN)
                    ],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->nik
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create closed konsultasi notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get admin users by target level - FIXED TO RETURN OBJECTS
     */
    private function getAdminUsersByTarget($target): array
    {
        try {
            Log::info('Getting admin users by target', ['target' => $target]);
            
            $users = User::whereHas('pengurus', function($query) use ($target) {
                $query->whereHas('role', function($roleQuery) use ($target) {
                    switch($target) {
                        case 'DPD':
                            $roleQuery->where('NAME', 'ADMIN_DPD');
                            break;
                        case 'DPW':
                            $roleQuery->where('NAME', 'ADMIN_DPW');
                            break;
                        case 'DPP':
                            $roleQuery->where('NAME', 'ADMIN_DPP');
                            break;
                        case 'GENERAL':
                        default:
                            $roleQuery->whereIn('NAME', ['ADM', 'ADMIN_DPP']);
                    }
                });
            })
            ->with(['pengurus.role'])
            ->get();
            
            Log::info('Admin users found', [
                'target' => $target,
                'count' => $users->count(),
                'users' => $users->pluck('nik')->toArray()
            ]);
            
            // Return as array of User objects (not ->toArray())
            return $users->all();
            
        } catch (\Exception $e) {
            Log::error('Error getting admin users by target', [
                'target' => $target,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return fallback admin if available
            $fallbackAdmin = User::whereHas('pengurus.role', function($query) {
                $query->where('NAME', 'ADM');
            })->first();
            
            return $fallbackAdmin ? [$fallbackAdmin] : [];
        }
    }

    /**
     * Get commenter name safely
     */
    private function getCommenterName($commentBy): string
    {
        if (is_object($commentBy)) {
            if (property_exists($commentBy, 'V_NAMA_KARYAWAN')) {
                return $commentBy->V_NAMA_KARYAWAN;
            } elseif (property_exists($commentBy, 'name')) {
                return $commentBy->name;
            }
        } elseif (is_array($commentBy)) {
            return $commentBy['V_NAMA_KARYAWAN'] ?? $commentBy['name'] ?? 'Unknown User';
        }
        
        return 'Unknown User';
    }

    /**
     * Get admin level label
     */
    private function getAdminLevelByTarget($target): string
    {
        return match($target) {
            'DPD' => 'DPD (Dewan Pengurus Daerah)',
            'DPW' => 'DPW (Dewan Pengurus Wilayah)', 
            'DPP' => 'DPP (Dewan Pengurus Pusat)',
            'GENERAL' => 'Admin SEKAR',
            default => 'Admin SEKAR'
        };
    }
}