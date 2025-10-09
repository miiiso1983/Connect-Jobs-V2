<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\PushNotification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private $messaging;

    public function __construct()
    {
        try {
            $factory = (new Factory);
            
            // Use service account credentials if available
            $credentialsPath = config('services.firebase.credentials');
            if ($credentialsPath && file_exists($credentialsPath)) {
                $factory = $factory->withServiceAccount($credentialsPath);
            } else {
                // Use project ID for default credentials
                $projectId = config('services.firebase.project_id');
                if ($projectId) {
                    $factory = $factory->withProjectId($projectId);
                }
            }
            
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase messaging: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Send notification to specific user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], string $type = null): bool
    {
        if (!$this->messaging) {
            Log::error('Firebase messaging not initialized');
            return false;
        }

        // Get active FCM tokens for the user
        $fcmTokens = $user->activeFcmTokens()->pluck('fcm_token')->toArray();
        
        if (empty($fcmTokens)) {
            Log::info("No active FCM tokens found for user {$user->id}");
            return false;
        }

        // Create notification record
        $notification = PushNotification::create([
            'user_id' => $user->id,
            'type' => $type ?? 'general',
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'status' => PushNotification::STATUS_PENDING,
        ]);

        try {
            $sentTokens = [];
            $failedTokens = [];

            foreach ($fcmTokens as $token) {
                try {
                    $message = $this->createMessage($token, $title, $body, $data);
                    $this->messaging->send($message);
                    $sentTokens[] = $token;
                    
                    // Update last_used_at for successful tokens
                    UserFcmToken::where('fcm_token', $token)->update(['last_used_at' => now()]);
                    
                } catch (\Exception $e) {
                    Log::warning("Failed to send notification to token {$token}: " . $e->getMessage());
                    $failedTokens[] = $token;
                    
                    // Deactivate invalid tokens
                    if ($this->isInvalidTokenError($e)) {
                        UserFcmToken::where('fcm_token', $token)->update(['is_active' => false]);
                    }
                }
            }

            if (!empty($sentTokens)) {
                $notification->markAsSent($sentTokens);
                Log::info("Notification sent successfully to user {$user->id}. Tokens: " . count($sentTokens));
                return true;
            } else {
                $notification->markAsFailed('All tokens failed: ' . implode(', ', $failedTokens));
                return false;
            }

        } catch (\Exception $e) {
            $notification->markAsFailed($e->getMessage());
            Log::error("Failed to send notification to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToUsers(array $users, string $title, string $body, array $data = [], string $type = null): array
    {
        $results = [];
        
        foreach ($users as $user) {
            if ($user instanceof User) {
                $results[$user->id] = $this->sendToUser($user, $title, $body, $data, $type);
            }
        }
        
        return $results;
    }

    /**
     * Send notification to all admin users
     */
    public function sendToAdmins(string $title, string $body, array $data = [], string $type = null): array
    {
        $admins = User::where('role', 'admin')
                     ->where('status', 'active')
                     ->whereHas('activeFcmTokens')
                     ->get();
        
        return $this->sendToUsers($admins, $title, $body, $data, $type);
    }

    /**
     * Create FCM message
     */
    private function createMessage(string $token, string $title, string $body, array $data = []): CloudMessage
    {
        $notification = Notification::create($title, $body);
        
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        // Add custom data if provided
        if (!empty($data)) {
            $message = $message->withData($data);
        }

        // Configure for Android
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
        ]);
        $message = $message->withAndroidConfig($androidConfig);

        // Configure for iOS
        $apnsConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'alert' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'sound' => 'default',
                    'badge' => 1,
                ],
            ],
        ]);
        $message = $message->withApnsConfig($apnsConfig);

        return $message;
    }

    /**
     * Check if the error indicates an invalid token
     */
    private function isInvalidTokenError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'invalid registration token') ||
               str_contains($message, 'registration token not registered') ||
               str_contains($message, 'invalid argument');
    }

    /**
     * Test notification sending capability
     */
    public function testConnection(): bool
    {
        return $this->messaging !== null;
    }
}
