<?php

use App\Console\Commands\ArchiveCompletedRequests;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * FEATURE: Request History & Archiving
 * Schedule automatic archiving of completed requests every hour
 * Requests are archived after 24 hours from first view
 */
Schedule::command(ArchiveCompletedRequests::class)
    ->hourly()
    ->name('archive-completed-requests')
    ->withoutOverlapping();
