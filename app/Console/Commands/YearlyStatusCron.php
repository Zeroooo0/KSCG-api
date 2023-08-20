<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Compatitor;
use App\Models\EventSchedule;
use App\Models\SpecialPersonal;
use App\Models\User;
use Illuminate\Console\Command;

class YearlyStatusCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yearlyStatus:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command sets status to false (club Users, Clubs, Compatitors) on the first january of the year.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('user_type', '0')->update(['status' => 1]);
        Club::where('status', 1)->update(['status' => 1]);
        Compatitor::where('status', 1)->update(['status' => 0]);
        SpecialPersonal::where('role', '!=', 0)->update(['status' => 1]);
    }
}
