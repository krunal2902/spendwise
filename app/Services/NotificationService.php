<?php

namespace App\Services;

use App\Models\Notification;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification.
     */
    public function create(int $userId, string $title, string $message, string $type = 'general', array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        if ($notification && !$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Mark all unread notifications as read for a user.
     */
    public function markAllRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(int $userId, int $limit = 5)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
