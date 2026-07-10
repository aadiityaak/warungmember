<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushNotification;
use App\Models\Broadcast;
use App\Models\Member;
use App\Models\Notification;
use App\Models\PushSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Response;

class BroadcastController extends Controller
{
    public function index(): Response
    {
        $broadcasts = Broadcast::latest()->paginate(20);

        return inertia('admin/broadcasts/Index', [
            'broadcasts' => $broadcasts,
        ]);
    }

    public function create(): Response
    {
        $memberCount = Member::count();
        $pointsAbove5k = Member::where('total_points', '>', 5000)->count();
        $newMembers30 = Member::where('created_at', '>=', now()->subDays(30))->count();
        $depositAbove0 = Member::where('deposit_balance', '>', 0)->count();

        $iconOptions = [
            ['value' => 'promo', 'label' => 'Promo', 'd' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
            ['value' => 'voucher', 'label' => 'Voucher', 'd' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
            ['value' => 'poin', 'label' => 'Poin', 'd' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['value' => 'deposit', 'label' => 'Deposit', 'd' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['value' => 'umum', 'label' => 'Umum', 'd' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];

        return inertia('admin/broadcasts/Create', [
            'memberCount' => $memberCount,
            'segmentCounts' => [
                'all' => $memberCount,
                'points_above' => $pointsAbove5k,
                'new_member' => $newMembers30,
                'deposit_above' => $depositAbove0,
            ],
            'iconOptions' => $iconOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:notification,email',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'segment' => 'required|in:all,points_above,new_member,deposit_above',
            'segment_value' => 'nullable|integer|min:0',
            'icon' => 'required_if:type,notification|string|in:promo,voucher,poin,deposit,umum',
        ]);

        $query = Member::query()->with('user');

        if ($validated['segment'] === 'points_above') {
            $val = $validated['segment_value'] ?? 5000;
            $query->where('total_points', '>', $val);
        } elseif ($validated['segment'] === 'new_member') {
            $days = $validated['segment_value'] ?? 30;
            $query->where('created_at', '>=', now()->subDays($days));
        } elseif ($validated['segment'] === 'deposit_above') {
            $val = $validated['segment_value'] ?? 0;
            $query->where('deposit_balance', '>', $val);
        }

        $members = $query->get();
        $sentCount = 0;

        if ($validated['type'] === 'notification') {
            $notifications = [];
            foreach ($members as $member) {
                $notifications[] = [
                    'member_id' => $member->id,
                    'type' => $validated['icon'] ?? 'umum',
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'data' => null,
                    'read_at' => null,
                    'created_at' => now(),
                ];
            }

            if (! empty($notifications)) {
                Notification::insert($notifications);
            }
            $sentCount = count($notifications);

            // Send push notifications to subscribed members
            $memberIds = $members->pluck('id');
            $subscriptions = PushSubscription::whereIn('member_id', $memberIds)->get();
            $pushPayload = [
                'title' => $validated['title'],
                'body' => $validated['body'],
                'icon' => '/pwa-icons/pwa-192x192.png',
                'badge' => '/pwa-icons/pwa-192x192.png',
                'url' => '/member/notifications',
            ];
            foreach ($subscriptions as $subscription) {
                dispatch(new SendPushNotification($subscription, $pushPayload));
            }
        }

        if ($validated['type'] === 'email') {
            foreach ($members as $member) {
                if ($member->user?->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::html(
                            $validated['body'],
                            function (\Illuminate\Mail\Message $message) use ($member, $validated) {
                                $message->to($member->user->email)
                                    ->subject($validated['title']);
                            }
                        );
                        $sentCount++;
                    } catch (\Throwable) {
                        // skip
                    }
                }
            }
        }

        Broadcast::create([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'data' => [
                'segment' => $validated['segment'],
                'segment_value' => $validated['segment_value'],
            ],
            'sent_count' => $sentCount,
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.broadcasts.index')
            ->with('toast', ['type' => 'success', 'message' => "Broadcast terkirim ke {$sentCount} member."]);
    }

    public function estimateCount(Request $request): JsonResponse
    {
        $segment = $request->input('segment', 'all');
        $value = $request->integer('segment_value');

        $query = Member::query();

        if ($segment === 'points_above') {
            $val = $value ?: 5000;
            $query->where('total_points', '>', $val);
        } elseif ($segment === 'new_member') {
            $days = $value ?: 30;
            $query->where('created_at', '>=', now()->subDays($days));
        } elseif ($segment === 'deposit_above') {
            $val = $value ?: 0;
            $query->where('deposit_balance', '>', $val);
        }

        return response()->json(['count' => $query->count()]);
    }
}
