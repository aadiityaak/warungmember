<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $member = request()->user()->member;

        $notifications = collect();

        if ($member) {
            $notifications = $member->notifications()
                ->latest('created_at')
                ->paginate(20);

            // Mark all as read when page visited (optional behavior)
            // $member->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return inertia('member/notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return back();
    }
}
