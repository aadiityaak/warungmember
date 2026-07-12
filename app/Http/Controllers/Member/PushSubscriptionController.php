<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $member = $request->user()->member;

        $topic = 'wm-'.$member->id.'-'.Str::random(8);
        $token = Str::random(32);

        $sub = PushSubscription::updateOrCreate(
            ['member_id' => $member->id, 'platform' => 'web'],
            [
                'ntfy_topic' => $topic,
                'ntfy_token' => $token,
                'subscribed' => true,
                'user_agent' => $request->userAgent(),
            ],
        );

        return response()->json([
            'success' => true,
            'topic' => $sub->ntfy_topic,
            'server' => config('services.ntfy.server'),
        ]);
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
            'topic' => $sub?->ntfy_topic,
            'server' => $sub ? config('services.ntfy.server') : null,
        ]);
    }
}
