<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Run the job market skills sync every Monday at 9:00 AM.
Schedule::command('skills:sync-job-market')->weeklyOn(1, '09:00');