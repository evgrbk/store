<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CurrenciesRates;
use App\Console\Commands\GoodsAutoImport;
use App\Models\AutoImport;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CurrenciesRates::class,
        GoodsAutoImport::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(CurrenciesRates::class)->everyMinute();
        $schedule->command(GoodsAutoImport::class, [AutoImport::SCHEDULE_TWICE_A_DAY])->twiceDaily();
        $schedule->command(GoodsAutoImport::class, [AutoImport::SCHEDULE_DAILY])->daily();
        $schedule->command(GoodsAutoImport::class, [AutoImport::SCHEDULE_WEEKLY])->weekly();
        $schedule->command(GoodsAutoImport::class, [AutoImport::SCHEDULE_MONTHLY])->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
