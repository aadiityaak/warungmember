<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $branding = $this->branding();

        return response()->json([
            'name' => $branding['app_name'],
            'short_name' => $branding['app_name'],
            'description' => 'Aplikasi manajemen membership warung',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $branding['primary_color'],
            'lang' => 'id',
            'scope' => '/',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => '/pwa-icons/pwa-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/pwa-icons/pwa-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);
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
