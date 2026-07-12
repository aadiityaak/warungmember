<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        $fcmToken = $request->input('token');

        PushSubscription::updateOrCreate(
            ['member_id' => $member->id],
            [
                'fcm_token' => $fcmToken,
                'subscribed' => true,
                'platform' => 'web',
                'user_agent' => $request->userAgent(),
            ],
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        PushSubscription::where('member_id', $member->id)
            ->update(['subscribed' => false]);

        return response()->json(['success' => true]);
    }

    public function status(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        $sub = PushSubscription::where('member_id', $member->id)
            ->where('subscribed', true)
            ->first();

        return response()->json([
            'subscribed' => $sub !== null,
        ]);
    }
}
