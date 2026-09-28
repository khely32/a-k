<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| The database cache store and the database session driver never delete their
| own rows, so on a free-tier database with a hard storage cap they will
| eventually fill the disk and every write starts failing. Prune daily.
*/
Schedule::command('maintenance:prune')->dailyAt('03:17');
