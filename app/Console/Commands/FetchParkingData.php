<?php

namespace App\Console\Commands;

use App\Stad;
use App\Fetchers\FetcherInterface;

use Illuminate\Console\Command;

/**
 * Class FetchParkingData
 * @package App\Console\Commands
 *
 * @author Matthieu Calie <matthieu@calie.be>
 */
class FetchParkingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:parking:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch All parking data';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cities = Stad::where('fetch_data', true)
            ->get();

        foreach ($cities as $city) {
            /** @var FetcherInterface $class */
            $class = new $city->fetch_class();
            $class->getDataFromSource($city->url);
            $class->importData();
        }
    }
}
