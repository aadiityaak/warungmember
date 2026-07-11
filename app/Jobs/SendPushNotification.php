<?php

namespace App\Jobs;

use App\Models\Member;
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
        public Member $member,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $subscriptions = PushSubscription::where('member_id', $this->member->id)
            ->where('subscribed', true)
            ->get();

        foreach ($subscriptions as $subscription) {
            if ($subscription->ntfy_topic) {
                $this->sendNtfy($subscription);
            }

            if ($subscription->endpoint) {
                $this->sendWebPush($subscription);
            }
        }
    }

    private function sendNtfy(PushSubscription $subscription): void
    {
        $server = config('services.ntfy.server');

        if (! $server) {
            return;
        }

        try {
            $headers = [];
            if ($secret = config('services.ntfy.secret')) {
                $headers['Authorization'] = 'Bearer ' . $secret;
            }

            Http::withHeaders($headers)->post(rtrim($server, '/') . '/' . $subscription->ntfy_topic, [
                'topic' => $subscription->ntfy_topic,
                'title' => $this->payload['title'] ?? 'WarungMember',
                'message' => $this->payload['body'] ?? '',
                'tags' => [$this->payload['type'] ?? 'info'],
                'priority' => 4,
                'click' => $this->payload['url'] ?? route('member.notifications'),
                'icon' => $this->payload['icon'] ?? asset('pwa-icons/pwa-192x192.png'),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function sendWebPush(PushSubscription $subscription): void
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

            $sub = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'authToken' => $subscription->auth,
                'publicKey' => $subscription->p256dh,
            ]);

            $webPush->queueNotification(
                $sub,
                json_encode($this->payload),
                ['TTL' => 86400],
            );

            $responses = $webPush->flush();

            foreach ($responses as $response) {
                if ($response->isExpired()) {
                    $subscription->delete();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
