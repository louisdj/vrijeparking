<?php

namespace App\Console\Commands;

use App\Parking;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class pullParkingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pullParkingData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parkeerdata ophalen';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $json = file_get_contents('http://datatank.stad.gent/4/mobiliteit/bezettingparkingsrealtime.json');
        $gent = json_decode($json);

        foreach($gent as $parking)
        {
            Parking::where('naam', strtolower($parking->description))->update(['beschikbare_plaatsen' => $parking->parkingStatus->availableCapacity]);
        }
    }
}
