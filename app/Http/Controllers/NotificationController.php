<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Konsultasi; // <-- TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get user notifications (AJAX)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $notifications = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $unreadCount = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->whereNull('read_at')
                ->count();

            $formattedNotifications = $notifications->map(function($notification) {
                try {
                    if (empty($notification->data)) {
                        throw new \Exception('Notification data is empty or invalid.');
                    }

                    // ======================= PERUBAHAN DIMULAI DI SINI =======================
                    $profilePictureUrl = null;
                    $userInitial = '?';

                    // Cek apakah ada ID konsultasi di data notifikasi
                    if (isset($notification->data['konsultasi_id'])) {
                        // Cari konsultasi berdasarkan ID
                        $konsultasi = Konsultasi::find($notification->data['konsultasi_id']);
                        
                        if ($konsultasi) {
                            // Cari user yang membuat konsultasi
                            $creator = User::where('nik', $konsultasi->N_NIK)->first();
                            if ($creator) {
                                // Jika user punya foto profil, buat URLnya
                                if ($creator->profile_picture) {
                                    $profilePictureUrl = asset('storage/profile-pictures/' . $creator->profile_picture);
                                }
                                // Ambil inisial nama
                                $userInitial = strtoupper(substr($creator->name, 0, 1));
                            }
                        }
                    }
                    // ======================= PERUBAHAN SELESAI DI SINI =======================

                    return [
                        'id' => $notification->id,
                        'message' => $notification->getMessage(),
                        'icon' => $notification->getIcon(),
                        'color' => $notification->getColor(),
                        'time_ago' => $notification->created_at->diffForHumans(),
                        'is_unread' => $notification->isUnread(),
                        'konsultasi_id' => $notification->data['konsultasi_id'] ?? null,
                        'profile_picture_url' => $profilePictureUrl, // <-- DATA BARU
                        'user_initial' => $userInitial,           // <-- DATA BARU
                    ];
                } catch (\Exception $e) {
                    Log::error('Error processing notification for API', [
                        'notification_id' => $notification->id,
                        'error' => $e->getMessage(),
                    ]);
                    return null;
                }
            })->filter()->values();

            return response()->json([
                'success' => true,
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Critical error in notification index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_nik' => Auth::user()->nik ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat notifikasi.',
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    // ... sisa controller (markAsRead, markAllAsRead, getUnreadCount) tidak perlu diubah ...
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('id', $id)
                ->where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                
                Log::info('Notification marked as read', [
                    'notification_id' => $id,
                    'user_nik' => $user->nik
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }

            Log::warning('Notification not found for mark as read', [
                'notification_id' => $id,
                'user_nik' => $user->nik
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'user_nik' => $user->nik ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            
            $updated = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_nik' => $user->nik,
                'updated_count' => $updated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
                'updated_count' => $updated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'error' => $e->getMessage(),
                'user_nik' => $user->nik ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            
            $count = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->whereNull('read_at')
                ->count();

            Log::info('Unread count retrieved', [
                'user_nik' => $user->nik,
                'unread_count' => $count
            ]);

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting unread count', [
                'error' => $e->getMessage(),
                'user_nik' => $user->nik ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'unread_count' => 0
            ], 500);
        }
    }
}