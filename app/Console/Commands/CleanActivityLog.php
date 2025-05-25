<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;

class CleanActivityLog extends Command
{
    protected $signature = 'log:clean';

    protected $description = 'Hapus log aktivitas lebih dari 3 bulan';

    public function handle(): int
    {
        dd();
        $deleted = Activity::where('created_at', '<', now()->subDays(1))->delete();
        // $deleted = Activity::where('created_at', '<', now()->subMonths(3))->delete();

        Log::info("CleanActivityLog: $deleted log dihapus.");
        $this->info("Berhasil menghapus $deleted log.");

        return Command::SUCCESS;
    }
}
