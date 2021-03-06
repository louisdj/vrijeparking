<?php

namespace App\Http\Controllers;

use App\Log;
use App\mobiele_stad;
use App\Parking;
use App\Stad;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function steden()
    {
        Log::create([]);

        return response()->json(array(
            Stad::where('live_data', 1)->get()
        ));


    }

    public function stad($stad)
    {
        return response()->json(array(
            Stad::where('stad', $stad)->get()
        ));
    }

    public function parking($parking)
    {
        $parking = Parking::where('naam', $parking)->first();

        return response()->json(array(
            $parking,
            $parking->openingsuren,
            $parking->betaalmogelijkheden
        ));
    }


    public function parkings($stad)
    {
        if($stad == "all") {
            $parkings = Parking::all();
        } else {
            $parkings = Parking::where('stad', $stad)->get();
        }

        $response = response()->json(array(
            $parkings
        ));

        return $response;
    }

    public function lokatie($lat, $Lng)
    {
        return response()->json(array(
            $parkings = DB::table('parkings')
                ->whereBetween('latitude', [$lat - 0.007, $lat + 0.007])
                ->whereBetween('longitude', [$Lng - 0.007, $Lng + 0.007])
                ->get()
        ));
    }

    //jamaar zo nie eh

    public function vindStad($lat, $Lng)
    {
        $json = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='. $lat .',' . $Lng . '');
        $data = json_decode($json);



        $stad = mobiele_stad::where('naam', substr($data->results[1]->formatted_address, 0, strpos($data->results[1]->formatted_address, ',')))->first();

        if(!empty($stad)) {
            return response()->json(array(
                'status' => !empty($stad),
                'naam' => $data->results[1]->formatted_address,
                'code' => $stad->code,
                'code_compleet' => $stad->code_compleet
            ));
        } else {
            return response()->json(array(
                'status' => 'false'
            ));
        }
    }


    public function twitter($stad)
    {
        return response()->json(array(
            'error' => 'false',
            'parkings' => Parking::where('stad', $stad)->get()
        ));
    }

    public function chat($stad, $parking)
    {
        $parking = Parking::where('naam', 'like' , '%'.$parking.'%')->where('stad', $stad)->first();

        return response()->json(array(
            'messages' => array(
                [
                    'text' => 'Er zijn nog '. $parking->beschikbare_plaatsen .' plaatsen beschikbaar in parking ' . $parking->naam
                ])
        ));
    }

    public function chat_stad($stad)
    {
        $parkings = Parking::where('stad', $stad)->get();

        $array = [];

        foreach($parkings as $parking)
        {
            $var = array('url' => 'http://www.vrijeparking.be/api/chat/'.$parking->stad.'/'.$parking->naam, 'type' => 'json_plugin_url', 'title' => ''.$parking->naam.'');

            array_push($array, $var);
        }


        return response()->json(array(
            'messages' => array(
                'attachment' => [
                    'payload' => [
                        'template_type' => 'button',
                        'text' => 'Kies uw parking',
                        'buttons' =>

                                $array
//                                'url' => 'http://www.vrijeparking.be/api/chat/stad/parking',
//                                'type' => 'json_plugin_url',
//                                'title' => 'naam'



                    ],
                    'type' => 'template'
                    ]
            )
        ));
    }
}
