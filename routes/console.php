<?php

// use Illuminate\Console\Scheduling\Schedule;

use App\Models\Activity;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function(){
    // Activity::where('created_at', '<', now()->subMonths(3))->delete();
    DB::table('_blanks')->insert([
        'created_by' => 1,
        'created_at' => NOW()
    ]);
})->everyTwoSeconds();
