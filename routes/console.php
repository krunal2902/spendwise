<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Budget carry-forward: runs on the 1st of every month at midnight
Schedule::command('budgets:carry-forward')->monthlyOn(1, '00:00');
