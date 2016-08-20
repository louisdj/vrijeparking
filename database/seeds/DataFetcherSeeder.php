<?php

use Illuminate\Database\Seeder;

class DataFetcherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Stad::where('stad', 'gent')
            ->update([
                'fetch_data' => true,
                'fetch_class' => \App\Fetchers\GentFetcher::class,
            ]);

        // old data url: http://www.parkodata.be/OpenData/ParkoInfoNL.xml
        \App\Stad::where('stad', 'kortrijk')
            ->update([
                'url' => 'http://193.190.76.149:81/ParkoParkings/counters.php',
                'fetch_data' => true,
                'fetch_class' => \App\Fetchers\KortrijkFetcher::class,
            ]);
    }
}
