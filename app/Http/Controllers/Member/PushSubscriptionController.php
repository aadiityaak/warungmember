<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        $data = $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['member_id' => $member->id, 'endpoint' => $data['endpoint']],
            [
                'auth' => $data['keys']['auth'],
                'p256dh' => $data['keys']['p256dh'],
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function subscribeFcm(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        $data = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            [
                'member_id' => $member->id,
                'platform' => 'android',
            ],
            [
                'fcm_token' => $data['fcm_token'],
                'endpoint' => null,
                'auth' => null,
                'p256dh' => null,
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|url']);

        PushSubscription::where('endpoint', $request->endpoint)->delete();

        return response()->json(['success' => true]);
    }

    public function unsubscribeFcm(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        PushSubscription::where('member_id', $member->id)
            ->where('platform', 'android')
            ->delete();

        return response()->json(['success' => true]);
    }
}
