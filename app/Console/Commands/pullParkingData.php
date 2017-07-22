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
        //Cheat stukje voor Gent Sintpieters
        $content = file_get_contents("https://datatank.stad.gent/4/mobiliteit/bezettingparkeergaragesnmbs.json");
        $gsp_data = json_decode($content);

        Parking::where('naam', "Gent Sint-Pieters")
            ->update(['beschikbare_plaatsen' => $gsp_data[0]->parkingStatus->availableCapacity]);
    }
}
