<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create notification for new konsultasi (to admin)
     */
    public function notifyNewKonsultasi(Konsultasi $konsultasi): void
    {
        try {
            // Get admin users based on konsultasi target
            $adminUsers = $this->getAdminUsersByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK);
            
            Log::info('Found admin users for notification', [
                'konsultasi_id' => $konsultasi->ID,
                'target' => $konsultasi->TUJUAN,
                'specific' => $konsultasi->TUJUAN_SPESIFIK,
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
                            'admin_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                            'comment_by' => $commenterName
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $user->nik
                    ]);
                }
            } else {
                // Notify admin about user response - TARGET SPECIFIC ADMINS
                $adminUsers = $this->getAdminUsersByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK);
                
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'type' => 'comment',
                        'data' => [
                            'konsultasi_id' => $konsultasi->ID,
                            'jenis' => strtolower($konsultasi->JENIS),
                            'judul' => $konsultasi->JUDUL,
                            'admin_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK),
                            'comment_by' => $commenterName
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $admin->nik
                    ]);
                }
            }
            
            Log::debug('Comment notifications created', [
                'konsultasi_id' => $konsultasi->ID,
                'is_admin_comment' => $isAdminComment
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create comment notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create notification for escalation - FIXED VERSION
     */
    public function notifyEscalation(Konsultasi $konsultasi, array $escalationData): void
    {
        try {
            Log::info('Creating escalation notifications', [
                'konsultasi_id' => $konsultasi->ID,
                'escalation_data' => $escalationData
            ]);

            // Extract data from escalation context
            $escalationType = $escalationData['type'] ?? 'general';
            
            if ($escalationType === 'escalation_to_submitter') {
                // Notify original submitter about escalation
                $user = User::where('nik', $konsultasi->N_NIK)->first();
                if ($user) {
                    Notification::create([
                        'type' => 'escalate',
                        'data' => [
                            'konsultasi_id' => $konsultasi->ID,
                            'jenis' => strtolower($konsultasi->JENIS),
                            'judul' => $konsultasi->JUDUL,
                            'escalate_to' => $escalationData['escalated_to'] ?? 'level yang lebih tinggi',
                            'escalation_reason' => $escalationData['escalation_reason'] ?? 'Konsultasi dieskalasi'
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $user->nik
                    ]);
                    
                    Log::info('Escalation notification sent to submitter', [
                        'konsultasi_id' => $konsultasi->ID,
                        'submitter_nik' => $user->nik
                    ]);
                }
            } else {
                // Notify new target admins about the escalated konsultasi
                $newAdminUsers = $this->getAdminUsersByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK);
                $fromUser = $this->getSafeFromUser($konsultasi);
                
                foreach ($newAdminUsers as $admin) {
                    Notification::create([
                        'type' => 'new',
                        'data' => [
                            'konsultasi_id' => $konsultasi->ID,
                            'jenis' => strtolower($konsultasi->JENIS),
                            'judul' => $konsultasi->JUDUL,
                            'from_user' => $fromUser,
                            'target_level' => $konsultasi->TUJUAN,
                            'escalated' => true,
                            'escalated_from' => $escalationData['escalated_from'] ?? 'level sebelumnya',
                            'escalation_reason' => $escalationData['escalation_reason'] ?? 'Konsultasi dieskalasi'
                        ],
                        'notifiable_type' => User::class,
                        'notifiable_id' => $admin->nik
                    ]);
                }
                
                Log::info('Escalation notifications sent to new target admins', [
                    'konsultasi_id' => $konsultasi->ID,
                    'new_target' => $konsultasi->TUJUAN,
                    'new_specific' => $konsultasi->TUJUAN_SPESIFIK,
                    'new_admin_count' => count($newAdminUsers)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create escalation notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create notification for closed konsultasi - UPDATED VERSION
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
                        'closed_by_level' => $this->getAdminLevelByTarget($konsultasi->TUJUAN, $konsultasi->TUJUAN_SPESIFIK)
                    ],
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->nik
                ]);
            }
            
            Log::debug('Close notification created', [
                'konsultasi_id' => $konsultasi->ID,
                'user_nik' => $konsultasi->N_NIK
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create closed konsultasi notification', [
                'konsultasi_id' => $konsultasi->ID,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get admin users by target level - FIXED TO HANDLE SPECIFIC TARGETS
     */
    private function getAdminUsersByTarget($target, $targetSpecific = null): array
    {
        try {
            Log::info('Getting admin users by target', [
                'target' => $target,
                'target_specific' => $targetSpecific
            ]);
            
            $query = User::whereHas('pengurus', function($query) use ($target, $targetSpecific) {
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
                
                // Add specific target filtering
                if ($targetSpecific && $target !== 'GENERAL') {
                    switch($target) {
                        case 'DPD':
                            $query->where('DPD', $targetSpecific);
                            break;
                        case 'DPW':
                            $query->where('DPW', $targetSpecific);
                            break;
                        case 'DPP':
                            // DPP tidak perlu filter spesifik
                            break;
                    }
                }
            })
            ->with(['pengurus.role']);
            
            // Jika tidak ada hasil dengan target spesifik, ambil semua admin dari level tersebut
            $users = $query->get();
            
            if ($users->isEmpty() && $targetSpecific) {
                Log::warning('No specific admin found, falling back to general level admins', [
                    'target' => $target,
                    'target_specific' => $targetSpecific
                ]);
                
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
            }
            
            Log::info('Admin users found', [
                'target' => $target,
                'target_specific' => $targetSpecific,
                'count' => $users->count(),
                'users' => $users->pluck('nik')->toArray()
            ]);
            
            // Return as array of User objects (not ->toArray())
            return $users->all();
            
        } catch (\Exception $e) {
            Log::error('Error getting admin users by target', [
                'target' => $target,
                'target_specific' => $targetSpecific,
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
     * Get admin level label by target
     */
    private function getAdminLevelByTarget($target, $targetSpecific = null): string
    {
        switch($target) {
            case 'DPD':
                return $targetSpecific ? "Admin DPD {$targetSpecific}" : 'Admin DPD';
            case 'DPW':
                return $targetSpecific ? "Admin DPW {$targetSpecific}" : 'Admin DPW';
            case 'DPP':
                return 'Admin DPP';
            case 'GENERAL':
            default:
                return 'SEKAR Pusat';
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
            return $commentBy['V_NAMA_KARYAWAN'] ?? $commentBy['name'] ?? 'Admin';
        } elseif (is_string($commentBy)) {
            return $commentBy;
        }
        
        return 'Admin';
    }

    /**
     * Get safe from user
     */
    private function getSafeFromUser($konsultasi): string
    {
        try {
            return $konsultasi->karyawan->V_NAMA_KARYAWAN ?? $konsultasi->N_NIK ?? 'User';
        } catch (\Exception $e) {
            return 'User';
        }
    }
}