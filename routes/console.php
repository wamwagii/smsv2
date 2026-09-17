<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('invoices:sync')->dailyAt('02:00');
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/** # Safe preview — no changes made
php artisan invoices:sync --dry-run

# Actually fix any drift
php artisan invoices:sync */