<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\Member;
use App\Models\PushSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Member $member,
        public array $payload,
        public ?int $broadcastId = null,
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

            $response = Http::withHeaders($headers)->post(rtrim($server, '/') . '/' . $subscription->ntfy_topic, [
                'topic' => $subscription->ntfy_topic,
                'title' => $this->payload['title'] ?? 'WarungMember',
                'message' => $this->payload['body'] ?? '',
                'tags' => [$this->payload['type'] ?? 'info'],
                'priority' => 4,
                'click' => $this->payload['url'] ?? route('member.notifications'),
                'icon' => $this->payload['icon'] ?? asset('pwa-icons/pwa-192x192.png'),
            ]);

            $this->logDelivery(result: $response->successful());
        } catch (\Throwable $e) {
            $this->logDelivery(result: false);

            report($e);
        }
    }

    private function logDelivery(bool $result): void
    {
        if (! $this->broadcastId) {
            return;
        }

        $broadcast = Broadcast::find($this->broadcastId);
        if (! $broadcast) {
            return;
        }

        $log = $broadcast->delivery_log ?? [
            'total_push_attempts' => 0,
            'ntfy_success' => 0,
            'ntfy_failed' => 0,
        ];

        $log['total_push_attempts']++;

        if ($result) {
            $log['ntfy_success']++;
        } else {
            $log['ntfy_failed']++;
        }

        $broadcast->update(['delivery_log' => $log]);
    }
}
