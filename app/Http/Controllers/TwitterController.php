<?php

namespace App\Http\Controllers;

use App\Parking;
use App\Twitter_bericht;
use App\TwitterParking;
use Illuminate\Http\Request;

use App\Http\Requests;
use Twitter;

class TwitterController extends Controller
{
    public function start()
    {
        $parkings = Parking::where('stad', 'gent')->where('live_data', 1)->get();

        //Overloop alle parkings die live data vergaren
        foreach($parkings as $parking) {

            //Als een parking minder dan 30% heeft of reeds in de lijst zit ga dan verder
            if(((($parking->beschikbare_plaatsen / $parking->totaal_plaatsen) *100) < 30) || TwitterParking::where('naam', $parking->naam)->first()){

                //Als een nieuw gevonden parking nog niet in de lijst staat, plaats hem er dan in
                if(!TwitterParking::where('naam', $parking->naam)->first()) {

                    $twitter_parking = new TwitterParking();

                    $twitter_parking->naam = $parking->naam;
                    $twitter_parking->stad = $parking->stad;
                    $twitter_parking->beschikbare_plaatsen = $parking->beschikbare_plaatsen;
                    $twitter_parking->totaal_plaatsen = $parking->totaal_plaatsen;

                    $twitter_parking->save();
                }
                //Als deze er wel al instaat, update dan de parking met nieuwe plaatsen
                else {
                    $twitter_parking = TwitterParking::where('naam', $parking->naam)->first();

                    $twitter_parking->beschikbare_plaatsen = $parking->beschikbare_plaatsen;

                    $twitter_parking->save();
                }
            }
        }

        //Loop door bestaande items om te zien of er terug parkings meer dan 30% hebben of om te zien of ze helemaal volzet zijn
        foreach(TwitterParking::all() as $twitter_parking)
        {
            if((($twitter_parking->beschikbare_plaatsen / $twitter_parking->totaal_plaatsen) *100) > 30) {
                Twitter::postTweet(['status' => 'Joepie! Terug meer dan 30% vrije parkeerplaats in parking ' . $twitter_parking->naam . ' ('.$twitter_parking->beschikbare_plaatsen.'/'.$twitter_parking->totaal_plaatsen.')' , 'format' => 'json']);
                $twitter_parking->delete();
            }

            if((($twitter_parking->beschikbare_plaatsen / $twitter_parking->totaal_plaatsen) *100) <= 4) {
                Twitter::postTweet(['status' => 'Helaas! Parking ' . $twitter_parking->naam . ' is helemaal volzet!' , 'format' => 'json']);

                $twitter_parking->tweeted = 1;
                $twitter_parking->save();
            }
        }

        //Kijk of er nog parkings in de te tweeten lijst staan die nog niet getweet werden
        $twitter_parking = TwitterParking::all()->where('stad', 'gent')->where('tweeted', 0)->first();

        //Indien de lijst niet leeg is
        if($twitter_parking) {
            $random = rand(0, 100);

            //Single tweet 60% kans
            if($random < 60) {
                Twitter::postTweet(['status' => 'Minder dan 30% vrije parkeerplaats in parking ' . $twitter_parking->naam . ' ('.$twitter_parking->beschikbare_plaatsen.'/'.$twitter_parking->totaal_plaatsen.')' , 'format' => 'json']);
            } //Summary tweet 30% kans
            elseif($random <100) {
                $string = "";
                foreach(TwitterParking::all() as $parking) {
                    $string .= "- " . $parking->naam;
                }

                Twitter::postTweet(['status' => 'Stad #Gent minder dan 30% vrijeparkeerplaats in:\n' . $string , 'format' => 'json']);
            }
            $twitter_parking->tweeted = 1;
            $twitter_parking->save();
        }
    }
}
