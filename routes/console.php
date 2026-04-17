<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('saas:mensalidades-sync')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('portal:sync-dentists --chunk=200')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('portal:send-appointment-confirmations --hours=72 --chunk=100')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('portal:send-appointment-reminders --windows=1440,180,60 --tolerance=10 --chunk=200')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('portal:monitor-whatsapp-delivery --minutes=30 --min-attempts=10 --max-failure-rate=0.30')
    ->everyTenMinutes()
    ->withoutOverlapping();
