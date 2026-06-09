<?php

use App\Jobs\SendAlertesGarantieJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendAlertesGarantieJob())
    ->daily()
    ->name('send-alertes-garantie')
    ->withoutOverlapping();

