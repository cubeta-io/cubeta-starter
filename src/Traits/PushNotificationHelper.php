<?php

namespace Cubeta\CubetaStarter\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

trait PushNotificationHelper
{
    /**
     * This function send notification to every user has fcm token
     * In case of failure exception will be logged with this message ERROR SENDING FCM
     * @param $notification
     * @param array $data
     */
    public function sendToAllUsers($notification, array $data): void
    {
        try {
            $users = User::whereNotNull('fcm_token')->get();
            Notification::send($users, new $notification($data));
        } catch (\Exception $exception) {
            Log::info('ERROR SENDING FCM');
            Log::info($exception->getMessage());
        }
    }

    /**
     * send notification to many users
     * In case of failure exception will be logged with this message ERROR SENDING FCM
     * @param mixed $notification notification
     * @param array $data notification data
     * @param array $users_ids users ids
     */
    public function sendToManyUsers($notification, array $data, array $users_ids): void
    {
        try {
            $users = User::whereNotNull('fcm_token')->whereIn('user_id', $users_ids)->get();
            Notification::send($users, new $notification($data));
        } catch (\Exception $exception) {
            Log::info('ERROR SENDING FCM');
            Log::info($exception->getMessage());
        }
    }

    /**
     * send notification if user has fcm token
     * In case of failure exception will be logged with this message ERROR SENDING FCM
     * @param mixed $notification notification
     * @param array $data notification data
     * @param User $user user
     */
    public function sendSingleUser(mixed $notification, array $data, User $user): void
    {
        if ($user->fcm_token) {
            try {
                Notification::send($user, new $notification($data));
            } catch (\Exception $exception) {
                Log::info('ERROR SENDING FCM');
                Log::info($exception->getMessage());
            }
        }
    }
}
