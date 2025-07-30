<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
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
            
            Log::info('Notification API called', [
                'user_id' => $user->id,
                'user_nik' => $user->nik,
                'request_headers' => $request->headers->all()
            ]);
            
            $notifications = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $unreadCount = Notification::where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)  // FIXED: Use User::class
                ->whereNull('read_at')
                ->count();

            Log::info('Notifications found', [
                'user_nik' => $user->nik,
                'notifications_count' => $notifications->count(),
                'unread_count' => $unreadCount,
                'notifications_data' => $notifications->toArray()
            ]);

            $response = [
                'success' => true,
                'notifications' => $notifications->map(function($notification) {
                    try {
                        return [
                            'id' => $notification->id,
                            'message' => $notification->getMessage(),
                            'icon' => $notification->getIcon(),
                            'color' => $notification->getColor(),
                            'time_ago' => $notification->created_at->diffForHumans(),
                            'is_unread' => $notification->isUnread(),
                            'konsultasi_id' => isset($notification->data['konsultasi_id']) ? $notification->data['konsultasi_id'] : null,
                            'data' => $notification->data
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error processing notification', [
                            'notification_id' => $notification->id,
                            'error' => $e->getMessage(),
                            'data' => $notification->data
                        ]);
                        // Return null for broken notifications
                        return null;
                    }
                })->filter()->values(),  // FIXED: Remove null values and reindex
                'unread_count' => $unreadCount
            ];

            Log::info('API Response prepared', ['response_count' => count($response['notifications'])]);

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Critical error in notification index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_nik' => $user->nik ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading notifications: ' . $e->getMessage(),
                'notifications' => [],
                'unread_count' => 0
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::user();
            
            $notification = Notification::where('id', $id)
                ->where('notifiable_id', $user->nik)
                ->where('notifiable_type', User::class)  // FIXED: Use User::class
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
                ->where('notifiable_type', User::class)  // FIXED: Use User::class
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