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
                ->orderByRaw('read_at IS NULL DESC')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return inertia('member/notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead()
    {
        $member = request()->user()->member;

        if ($member) {
            $member->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return back()
            ->with('toast', ['type' => 'success', 'message' => 'Semua notifikasi sudah dibaca.']);
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return back();
    }
}
