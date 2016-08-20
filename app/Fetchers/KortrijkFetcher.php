<?php

namespace App\Fetchers;

use App\Parking;
use App\ParkingHistory;

/**
 * Class KortrijkFetcher
 * @package App\Fetchers
 *
 * @author Matthieu Calie <matthieu@calie.be>
 */
class KortrijkFetcher implements FetcherInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param string $source
     */
    public function getDataFromSource($source)
    {
        try {
            $this->data = simplexml_load_file($source);
        } catch(\Exception $e) {
            $this->data = [];
        }
    }

    /**
     * @return mixed
     */
    public function importData()
    {
        /** @var \SimpleXMLElement $parking */
        foreach ($this->data as $parking) {
            // update parking data
            Parking::where('naam', $parking)
                ->update([
                    'beschikbare_plaatsen' => $parking->attributes()['vrij'],
                ]);

            //get the parking id
            $parkingId = Parking::where('naam', $parking)->first();

            // Create the parking history
            ParkingHistory::create([
                'parking_id' => $parkingId->id,
                'bezetting' => $parking['bezet'],
            ]);
        }
    }
}
