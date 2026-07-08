<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            html {
                background-color: oklch(1 0 0);
            }
        </style>

        @php
            $branding = $page['props']['branding'] ?? [];
            $appName = $branding['app_name'] ?? config('app.name', 'Laravel');
            $favicon = $branding['favicon_url'] ?? null;
        @endphp

        @if($favicon)
            <link rel="icon" href="{{ $favicon }}" type="image/png">
        @else
            <link rel="icon" href="/logo/logo-mas-mbull-favicon.png" type="image/png">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @routes

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $appName }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
