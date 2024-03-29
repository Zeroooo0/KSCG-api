<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $comands = [
        Commands\YearlyStatusCron::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('sanctum:prune-expired --hours=24')->everyTwoMinutes();
        // $schedule->command('inspire')->hourly();
        $schedule->command('yearlyStatus:cron')->evenInMaintenanceMode()->cron('* * * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
