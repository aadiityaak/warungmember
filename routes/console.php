<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process queue jobs setiap menit — cocok untuk shared hosting tanpa supervisor
Schedule::command('queue:work --stop-when-empty --max-time=30')
    ->everyMinute()
    ->withoutOverlapping();
