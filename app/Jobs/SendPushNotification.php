<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\Member;
use App\Models\PushSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

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
            if ($subscription->fcm_token) {
                $this->sendFcm($subscription);
            }
        }
    }

    private function sendFcm(PushSubscription $subscription): void
    {
        $credentials = config('services.firebase.credentials');

        if (empty($credentials)) {
            return;
        }

        $serviceAccount = json_decode($credentials, true);

        if (! $serviceAccount) {
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($serviceAccount);
            $messaging = $factory->createMessaging();

            $title = $this->payload['title'] ?? 'WarungMember';
            $body = $this->payload['body'] ?? '';
            $url = $this->payload['url'] ?? route('member.notifications');

            $message = CloudMessage::withTarget('token', $subscription->fcm_token)
                ->withNotification(Notification::create($title, $body))
                ->withWebPushConfig([
                    'fcm_options' => [
                        'link' => $url,
                    ],
                ]);

            $messaging->send($message);

            $this->logDelivery(result: true);
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
            'fcm_success' => 0,
            'fcm_failed' => 0,
        ];

        $log['total_push_attempts']++;

        if ($result) {
            $log['fcm_success']++;
        } else {
            $log['fcm_failed']++;
        }

        $broadcast->update(['delivery_log' => $log]);
    }
}
