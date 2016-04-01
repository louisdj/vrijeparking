<?php

namespace App\Http\Controllers;

use App\Parking;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
//use \XmlParser;
//use SoapBox\Formatter\Formatter;



class ParkingController extends Controller
{
    public function stad($stad)
    {
        $url = DB::select("select url from data_sources where stad = ?", [$stad]);

        if (empty($url)) {
            return redirect('/');
        }
        else if($stad == "kortrijk") {
            $parkings = Parking::all()->where('stad', 'kortrijk');

            return view('parking.kortrijk.stad', compact('stad','parkings'));
        }
        else {
            $json = file_get_contents($url[0]->url);
            $data = json_decode($json);
        }

//        $data = Parking::where('stad', $stad)->all();

        return view('parking.'.$stad.'.stad', compact('stad', 'data'));
    }

    public function parking($name)
    {
        $json = file_get_contents('http://datatank.stad.gent/4/mobiliteit/bezettingparkingsrealtime.json');
        $gent = json_decode($json);

        $json = file_get_contents('http://data.irail.be/Parkings/Brussels.json');
        $Brussel = json_decode($json);

        $parkingDb = Parking::where('naam', $name)->first();

        $result = DB::table('parkings_historie')
            ->select('bezetting')
            ->where('parking_id', $parkingDb->id)
            ->where('updated_at', '>', date('Y-m-d', strtotime('-7 days')).' 00:00:00')
            ->where('updated_at', '<', date('Y-m-d', strtotime('-6 days')).' 00:00:00')
            ->get();

        if($parkingDb->stad == "gent") {

            foreach($gent as $parking)
            {
                if(strtolower($parking->description) == $name)
                {
                    return view('parking.gent.index', compact('parking', 'parkingDb', 'result'));
                }
            }
        } else if($parkingDb->stad == "brussel") {
            foreach($Brussel->Brussels as $parking)
            {
                if(strtolower($parking->name_nl) == $name) {
                    return view('parking.brussel.index', compact('parking', 'parkingDb'));
                }
            }
        }

        return redirect()->back();
    }

    public function vindparking() {

        return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8]);
    }

    public function vindparkingpost(Request $request) {

        if($request->coordinates == null) {
            $searchFor = str_replace(",","+", $request->location);
            $searchFor = str_replace(" ","+", $searchFor);


            $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
            $data = json_decode($json);

            $lat = $data->results[0]->geometry->location->lat;
            $Lng = $data->results[0]->geometry->location->lng;
        }
        else {
            $lat = substr($request->coordinates, 0, strpos($request->coordinates, ','));
            $Lng = substr($request->coordinates, strpos($request->coordinates, ',') + 1);
        }

        $parkings = DB::table('parkings')
            ->whereBetween('latitude', [$lat - 0.007, $lat + 0.007])
            ->whereBetween('longitude', [$Lng - 0.007, $Lng + 0.007])
            ->get();

        //key: AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo

//        $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
//        $data = json_decode($json);
//        http://maps.googleapis.com/maps/api/distancematrix/json?origins=Vancouver+BC|Seattle&destinations=San+Francisco|Victoria+BC&mode=bicycling&language=fr-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo

//        https://maps.googleapis.com/maps/api/distancematrix/json?origins=
//        Ter+Platen+12+9000+gent|amakersstraat+12&destinations=Sint-Pietersplein+65+9000+gent&mode=bicycling&language=fr-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo


        return view('vindParking.index',
            ['mapCenter' => $lat .",". $Lng,
            'parkings' => $parkings,
            'zoom' => 16,
            'searchTerm' => $request->location]);
    }


    public function graph() {
        $result = DB::table('parkings_historie')
            ->select('bezetting')
            ->where('parking_id', 3)
            ->where('updated_at', '>', date('Y-m-d', strtotime('-3 days')).' 00:00:00')
            ->where('updated_at', '<', date('Y-m-d', strtotime('-2 days')).' 00:00:00')
            ->get();


//        $result = DB::select('b')

        return view('testGraph', compact('result'));
    }



    public function antwerpen() {

        return view('extra.antwerpen');
    }


    //Wordt gebruikt om eenvoudig alle data vd "Parkings" tabel te inserten
    public function enterData() {

//        $json = file_get_contents("http://data.irail.be/Parkings/brussels.json");
//        $data = json_decode($json);

        //gent
//        foreach($data as $parking) {
//            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude) values(?, ?, ?, ?, ?)" ,
//                [$parking->description,
//                    $parking->city->name,
//                    $parking->address,
//                    $parking->latitude,
//                    $parking->longitude]);
//        }

        //brussel
//        foreach($data->brussels as $parking) {
//            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude) values(?, ?, ?, ?, ?)" ,
//                [$parking->name_nl,
//                    "brussel",
//                    $parking->address_nl,
//                    $parking->latitude,
//                    $parking->longitude]);
//        }

    }
}
