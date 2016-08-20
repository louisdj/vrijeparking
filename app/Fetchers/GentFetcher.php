<?php

namespace App\Fetchers;

use App\Parking;
use App\ParkingHistory;

/**
 * Class GentFetcher
 * @package App\Fetchers
 *
 * @author Matthieu Calie <matthieu@calie.be>
 */
class GentFetcher implements FetcherInterface
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
        $content = file_get_contents($source);
        $this->data = json_decode($content);
    }

    /**
     * @return mixed
     */
    public function importData()
    {
        foreach($this->data as $parking) {
            // Update current available spots
            Parking::where('naam', strtolower($parking->description))
                ->update(['beschikbare_plaatsen' => $parking->parkingStatus->availableCapacity]);

            // Get the parking id
            $dbParking = Parking::where('naam', strtolower($parking->description))
                ->select(['id'])
                ->first();

            // Create a parking history row
            ParkingHistory::create([
                'parking_id' => $dbParking->id,
                'bezetting' => ($parking->parkingStatus->totalCapacity - $parking->parkingStatus->availableCapacity),
            ]);
        }
    }
}
