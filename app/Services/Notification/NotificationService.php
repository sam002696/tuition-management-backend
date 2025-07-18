<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Services\ResponseBuilder\ApiResponseService;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getNotificationsByUserId($userId)
    {

        $authUser = Auth::user();

        $user = User::find($userId);

        if (!$user) {
            return ApiResponseService::errorResponse('User not found.', 404);
        }

        if ($authUser->id !== $user->id) {
            abort(403, 'Unauthorized access to notifications.');
        }



        $notifications = $user->notifications()->latest()->get();

        return ApiResponseService::successResponse(
            ['notifications' => $notifications],
            'Notifications retrieved successfully.'
        );
    }

    public function markAllAsRead($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return ApiResponseService::errorResponse('User not found.', 404);
        }

        $user->unreadNotifications->markAsRead();

        return ApiResponseService::successResponse(
            null,
            'All notifications marked as read.'
        );
    }
}
