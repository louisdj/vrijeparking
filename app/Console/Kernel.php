<?php

namespace App\Console;

use App\Console\Commands\FetchParkingData;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        FetchParkingData::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fetch:parking:data')
            ->everyFiveMinutes();

        $schedule->command('pullParkingData')
            ->everyMinute();

//        $schedule->command('pullAndPushTwitterRobot')
//            ->everyFiveMinutes();
    }
}
