<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PushSubscription $subscription,
        public array $payload,
    ) {}

    public function handle(): void
    {
        if ($this->subscription->platform === 'android' && $this->subscription->fcm_token) {
            $this->sendFcm();
        } else {
            $this->sendWebPush();
        }
    }

    private function sendFcm(): void
    {
        $serverKey = config('services.fcm.server_key');
        if (! $serverKey) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.$serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $this->subscription->fcm_token,
                'notification' => [
                    'title' => $this->payload['title'] ?? 'WarungMember',
                    'body' => $this->payload['body'] ?? '',
                    'icon' => $this->payload['icon'] ?? '/pwa-icons/pwa-192x192.png',
                    'sound' => 'default',
                ],
                'data' => [
                    'url' => $this->payload['url'] ?? '/member/notifications',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);

            if ($response->json('failure') === 1) {
                $this->subscription->delete();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function sendWebPush(): void
    {
        try {
            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $webPush = new WebPush($auth);

            $subscription = Subscription::create([
                'endpoint' => $this->subscription->endpoint,
                'authToken' => $this->subscription->auth,
                'publicKey' => $this->subscription->p256dh,
            ]);

            $webPush->queueNotification(
                $subscription,
                json_encode($this->payload),
                ['TTL' => 86400]
            );

            $responses = $webPush->flush();

            foreach ($responses as $response) {
                if ($response->isExpired()) {
                    $this->subscription->delete();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
