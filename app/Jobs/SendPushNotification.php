<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
