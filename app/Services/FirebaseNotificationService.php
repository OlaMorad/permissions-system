<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\DeviceToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected $messaging;



public function messaging()
{
    if ($this->messaging) {
        return $this->messaging;
    }

    $credentialsFile = config('firebase.projects.app.credentials.credentials_file');
    $projectId = config('firebase.projects.app.credentials.project_id');

    if (!$credentialsFile || !$projectId) {
        throw new \Exception('Firebase credentials or project ID not set.');
    }

    $factory = (new \Kreait\Firebase\Factory)
        ->withServiceAccount($credentialsFile)
        ->withProjectId($projectId);

    return $this->messaging = $factory->createMessaging();
}





    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $deviceTokens = DeviceToken::where('user_id', $userId)->pluck('device_token')->toArray();
        if (empty($deviceTokens)) return false;

        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($notification)->withData($data);

        foreach ($deviceTokens as $token) {
            $this->messaging->send($message, $token);
        }

        return true;
    }

    public function sendToRole(string $roleName, string $title, string $body, array $data = [])
    {
        $users = User::role($roleName)->get();
        foreach ($users as $user) {
            $this->sendToUser($user->id, $title, $body, $data);
        }
        return true;
    }
}
