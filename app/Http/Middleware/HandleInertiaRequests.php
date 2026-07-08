<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $unreadCount = 0;
        if ($user = $request->user()) {
            $unreadCount = $user->member?->notifications()->whereNull('read_at')->count() ?? 0;
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'unreadNotifications' => $unreadCount,
            'branding' => $this->branding(),
        ];
    }

    private function branding(): array
    {
        $defaults = [
            'app_name' => config('app.name'),
            'logo_url' => null,
            'favicon_url' => null,
            'primary_color' => '#E22625',
            'whatsapp_number' => '081335405231',
        ];

        $data = Storage::exists('branding.json')
            ? json_decode(Storage::get('branding.json'), true)
            : [];

        $data = array_filter($data ?? [], fn ($v) => $v !== null && $v !== '');

        return array_merge($defaults, $data);
    }
}
