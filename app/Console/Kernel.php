<?php

namespace App\Console;

use App\Parking;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

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

        //Update parkings
        $schedule->call(function () {
            $json = file_get_contents('http://datatank.stad.gent/4/mobiliteit/bezettingparkingsrealtime.json');
            $gent = json_decode($json);

            foreach($gent as $parking)
            {
                Parking::where('naam', strtolower($parking->description))->update(['beschikbare_plaatsen' => ($parking->parkingStatus->availableCapacity)]);
                $id = DB::table('parkings')->select('id')->where('naam', strtolower($parking->description))->get();

                DB::table('parkings_historie')->insert([
                    ['parking_id' => $id[0]->id, 'bezetting' => ($parking->parkingStatus->totalCapacity - $parking->parkingStatus->availableCapacity)]
                ]);
            }

            foreach(Parking::all()->where('stad', 'kortrijk') as $parking)
            {
                $bezetting = file_get_contents('http://www.parko.be/drk_parko_realtime_info.php?parking='.str_replace(" ", "%20", $parking->naam).'');
                $bezetting = substr($bezetting,strpos($bezetting, 'availableSpacesText\u0022\u003E') + 31,3);

                Parking::where('naam', $parking->naam)->update(['beschikbare_plaatsen' => stripslashes($bezetting)]);

                DB::table('parkings_historie')->insert([
                    ['parking_id' => $parking->id, 'bezetting' => ($parking->totaal_plaatsen - stripslashes($bezetting))]
                ]);
            }
        })->everyFiveMinutes();
    }
}
