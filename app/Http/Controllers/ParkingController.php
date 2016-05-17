<?php

namespace App\Http\Controllers;

use App\Betaalmogelijkheden;
use App\Openingsuren;
use App\Parking;
use App\Tarief;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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
        $parking = Parking::where('naam', $name)->first();

        $historie = DB::table('parkings_historie')
        ->select('bezetting')
        ->where('parking_id', $parking->id)
        ->where('updated_at', '>', date('Y-m-d', strtotime('-7 days')).' 00:00:00')
        ->where('updated_at', '<', date('Y-m-d', strtotime('-6 days')).' 00:00:00')
        ->get();

        $historie2 = DB::table('parkings_historie')
            ->select('bezetting')
            ->where('parking_id', $parking->id)
            ->where('updated_at', '>', date('Y-m-d', strtotime('-14 days')).' 00:00:00')
            ->where('updated_at', '<', date('Y-m-d', strtotime('-13 days')).' 00:00:00')
            ->get();

        $b = $historie;
        $historieAverage = array();

        foreach ($historie2 as $key => $value)
        {
            if(isset($b[$key])) {
                array_push($historieAverage, ($b[$key]->bezetting + $value->bezetting) / 2);
            }
        }

        $openingsuren = Openingsuren::where('parking_id', $parking->id)->get();

        $parking_betaalmogelijkheden = Betaalmogelijkheden::where('parking_id', $parking->id)->get();

        $tarievenDag = Tarief::where('parking_id', $parking->id)->where('moment', 'dag')->get();
        $tarievenNacht = Tarief::where('parking_id', $parking->id)->where('moment', 'nacht')->get();


        return view('templates.parking_template',
            compact('parking', 'openingsuren', 'historie', 'historieAverage', 'parking_betaalmogelijkheden', 'tarievenDag', 'tarievenNacht'));
    }

    public function vindparking() {

        return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8]);
    }

    public function vindparkingpost(Request $request) {

        if($request->location == null && $request->coordinates == null) {
            return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8])->with('parkings', []);
        }
        else if($request->coordinates == null) {
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

        //Kotrijk
//        $xml=simplexml_load_file("http://193.190.76.149:81/ParkoParkings/counters.php") or die("Error: Cannot create object");
//
//        foreach($xml as $parking)
//        {
//            echo $parking;
//            echo $parking['capaciteit'];
//
//            echo "</br>";
//        }

//        $xml=simplexml_load_file("http://193.190.76.149:81/ParkoParkings/counters.php") or die("Error: Cannot create object");
//
//        foreach($xml as $parking)
//        {
//            Parking::where('naam', $parking)->update(['beschikbare_plaatsen' => stripslashes($parking['bezet'])]);
//            $parkingId = Parking::where('naam', $parking)->first();
//
//            DB::table('parkings_historie')->insert([
//                ['parking_id' => $parkingId->id, 'bezetting' => $parking['bezet']]
//            ]);
//        }
    }
}
