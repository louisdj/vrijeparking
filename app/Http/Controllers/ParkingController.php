<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ParkingController extends Controller
{
    public function stad($stad)
    {
        $url = DB::select("select url from data_sources where stad = ?", [$stad]);

        if (empty($url)) {
            return redirect('/');
        }
        else {
            $json = file_get_contents($url[0]->url);
            $data = json_decode($json);
        }

        return view('parking.'.$stad.'.stad', compact('stad', 'data'));
    }

    public function parking($name)
    {
        $json = file_get_contents('http://datatank.stad.gent/4/mobiliteit/bezettingparkingsrealtime.json');
        $data = json_decode($json);

        $json = file_get_contents('http://data.irail.be/Parkings/Brussels.json');
        $data2 = json_decode($json);

        foreach($data as $parking)
        {
            if($parking->description == $name)
            {
                return view('parking.'.$parking->city->name.'.index', compact('parking'));
            }
        }
        foreach($data2->Brussels as $parking)
        {
            if($parking->name_nl == $name) {
                return view('parking.brussel.index', compact('parking'));
            }
        }

        return redirect()->back();
    }

    public function vindparking() {

        return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8]);
    }

    public function vindparkingpost(Request $request) {

//        dd($request->location);
        $searchFor = str_replace(",","+", $request->location);
        $searchFor = str_replace(" ","+", $searchFor);

        $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
        $data = json_decode($json);

//        dd($data->results[0]->geometry->location->lat);
        $lat = $data->results[0]->geometry->location->lat;
        $Lng = $data->results[0]->geometry->location->lng;

        $parkings = DB::table('parkings')
            ->whereBetween('latitude', [$lat - 0.005, $lat + 0.005])
            ->whereBetween('longitude', [$Lng - 0.005, $Lng + 0.005])
            ->get();

        return view('vindparking.index',
            ['mapCenter' => "$lat, $Lng",
            'parkings' => $parkings,
            'zoom' => 16,
            'searchTerm' => $request->location]);
    }






    //Wordt gebruikt om eenvoudig alle data vd "Parkings" tabel te inserten
    public function enterData() {

//        $json = file_get_contents("http://data.irail.be/Parkings/Brussels.json");
//        $data = json_decode($json);

        //Gent
//        foreach($data as $parking) {
//            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude) values(?, ?, ?, ?, ?)" ,
//                [$parking->description,
//                    $parking->city->name,
//                    $parking->address,
//                    $parking->latitude,
//                    $parking->longitude]);
//        }

        //Brussel
//        foreach($data->Brussels as $parking) {
//            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude) values(?, ?, ?, ?, ?)" ,
//                [$parking->name_nl,
//                    "Brussel",
//                    $parking->address_nl,
//                    $parking->latitude,
//                    $parking->longitude]);
//        }

    }
}
