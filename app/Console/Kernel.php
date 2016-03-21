<?php

namespace App\Console;

use App\Parking;
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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $json = file_get_contents('http://datatank.stad.gent/4/mobiliteit/bezettingparkingsrealtime.json');
            $gent = json_decode($json);

            foreach($gent as $parking)
            {
                Parking::where('naam', strtolower($parking->description))->update(['beschikbare_plaatsen' => $parking->parkingStatus->availableCapacity]);
            }
        })->everyMinute();


//        $schedule->command('inspire')->hourly();
//        $schedule->command('pullParkingData')->everyMinute();
    }
}
