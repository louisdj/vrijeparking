<?php

namespace App\Http\Controllers;

use App\Betaalmogelijkheid;
use App\mindervalidenplaats;
use App\Openingsuren;
use App\Parking;
use App\Stad;
use App\Tarief;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class ParkingController extends Controller
{
    public function complete(Request $request)
    {
        $keywords = $request->get('term');
//
//        $suggestions = Parking::where('naam', 'LIKE', '%'.$keywords.'%')->get(['naam']);

        $results = array();

        $queries = DB::table('parkings')
            ->where('naam', 'LIKE', '%'.$keywords.'%')
            ->take(5)->get();

        foreach ($queries as $query)
        {
            $results[] = [ 'id' => $query->id, 'value' => $query->naam];
        }

        return response()->json($results);
    }

    public function stad($stad)
    {
        $parkings = Parking::all()->where('stad', strtolower($stad));
        $stad = Stad::where('stad', $stad)->first();

        return view('templates.stad_template', compact('stad', 'parkings'));
    }

    public function parking($name)
    {
        $parking = Parking::where('naam', $name)->first();

        $bezettingVandaag = DB::table('parkings_historie')
            ->select('bezetting')
            ->where('parking_id', $parking->id)
            ->where('updated_at', '>', date('Y-m-d').' 00:00:00')
            ->get();

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
        $parking_betaalmogelijkheden = Betaalmogelijkheid::where('parking_id', $parking->id)->get();

        $tarievenDag = Tarief::where('parking_id', $parking->id)->where('moment', 'dag')->orderBy('tijdsduur')->get();
        $tarievenNacht = Tarief::where('parking_id', $parking->id)->where('moment', 'nacht')->orderBy('tijdsduur')->get();


        return view('templates.parking_template',
            compact('parking', 'openingsuren', 'historie', 'historieAverage', 'parking_betaalmogelijkheden', 'tarievenDag', 'tarievenNacht', 'bezettingVandaag'));
    }

    public function vindparking_old() {

        return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8]);
    }

    public function vindparkingpost_old(Request $request)
    {
        if($request->location == null && $request->coordinates == null)
        {
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


//        dd($parkings);

        //key: AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo

//        $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
//        $data = json_decode($json);
//        http://maps.googleapis.com/maps/api/distancematrix/json?origins=Vancouver+BC|Seattle&destinations=San+Francisco|Victoria+BC&mode=bicycling&language=fr-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo

//        https://maps.googleapis.com/maps/api/distancematrix/json?origins=
//        Ter+Platen+12+9000+gent|amakersstraat+12&destinations=Sint-Pietersplein+65+9000+gent&mode=walking&language=fr-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo

//        $distance = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat . "," . $Lng . "&destinations=". $parkings[0]->latitude . "," . $parkings[0]->longitude . "&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo"));
//        $distanceResult = ($distance->rows[0]->elements[0]);
//
//        $afstand = $distanceResult->distance->text;
//        $tijdsduur = $distanceResult->duration->text;


        return view('vindParking.index',
            ['mapCenter' => $lat .",". $Lng,
            'parkings' => $parkings,
            'zoom' => 16,
            'searchTerm' => $request->location]);
    }


    public function vindparking() {

        $lat = "50.7755478";
        $Lng = "3.6038558";

        return view('vindParking.index', ['parkings' => [],'lat' => $lat, 'Lng' => $Lng, 'start' => "ja", 'zoom' => 9, 'nofooter' => 'true']);
    }

    public function vindparkingpost(Request $request, $coords = 0)
    {
        $zoom = 15;

        //Als er op de kaart geklikt wordt
        if($coords != 0)
        {
            $lat = substr($coords, 0, strpos($coords, ','));
            $Lng = substr($coords, strpos($coords, ',') + 1);


            $parkings = DB::table('parkings')
                ->whereBetween('latitude', [$lat - 0.015, $lat + 0.015])
                ->whereBetween('longitude', [$Lng - 0.015, $Lng + 0.015])
                ->get();

            if(count($parkings) > 0) {

                $origins_string = "";

                foreach ($parkings as $parking) {
                    $origins_string .= "" . $parking->latitude . "," . $parking->longitude . "|";
                }

                $json = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat . ',' . $Lng . '&destinations=' . $origins_string . '&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo');

                $data = json_decode($json);

                $afstanden = $data->rows[0]->elements;


                foreach ($afstanden as $key => $afstand) {
                    $parkings[$key]->afstand = $afstand->distance->value;

                    if(Tarief::where(['parking_id' => $parkings[$key]->id, 'tijdsduur' => '02:00:00'])->first()) {
                        $parkings[$key]->starttarief = Tarief::where(['parking_id' => $parkings[$key]->id, 'tijdsduur' => '02:00:00'])->first()->prijs;
                    }

                }

                uasort($parkings, array($this, 'sort_by_order'));

            } else {
                $parkings = [];
            }

            return view('vindParking.index', compact('parkings', 'zoom', 'lat', 'Lng'));
        }


        //Als er niets werd ingevuld in het zoekveld
        if($request->location == null && $request->coordinates == null)
        {
            return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558'])->with('parkings', [])->with('mindervalidenplaatsen', []);
        }
        //Als men een adres manueel intypt zonder selectie van google adviezen
        else if($request->coordinates == null)
        {
            $searchFor = str_replace(",","+", $request->location);
            $searchFor = str_replace(" ","+", $searchFor);

            $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
            $data = json_decode($json);

            if($data->status == "ZERO_RESULTS") {
                return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558'])->with('parkings', [])->with('mindervalidenplaatsen', []);
            } else {
                $lat = $data->results[0]->geometry->location->lat;
                $Lng = $data->results[0]->geometry->location->lng;
            }

        }
        //Als de coordinaten via google selectie omgezet worden en doorgestuurd
        else
        {
            $lat = substr($request->coordinates, 0, strpos($request->coordinates, ','));
            $Lng = substr($request->coordinates, strpos($request->coordinates, ',') + 1);
        }

        $parkings = DB::table('parkings')
            ->whereBetween('latitude', [$lat - 0.015, $lat + 0.015])
            ->whereBetween('longitude', [$Lng - 0.015, $Lng + 0.015])
            ->get();

        if(count($parkings) > 0)
        {

            $origins_string = "";

            foreach ($parkings as $parking) {
                $origins_string .= "" . $parking->latitude . "," . $parking->longitude . "|";
            }

            $json = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat . ',' . $Lng . '&destinations=' . $origins_string . '&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo');
            $data = json_decode($json);

            $afstanden = $data->rows[0]->elements;

            foreach ($afstanden as $key => $afstand)
            {
                $parkings[$key]->afstand = $afstand->distance->value;
                if(Tarief::where(['parking_id' => $parkings[$key]->id, 'tijdsduur' => '02:00:00'])->first())
                {
                    $parkings[$key]->starttarief = Tarief::where(['parking_id' => $parkings[$key]->id, 'tijdsduur' => '02:00:00'])->first()->prijs;
                }
            }

            uasort($parkings, array($this, 'sort_by_order'));

        } else {
            $parkings = [];
        }

        $nofooter = "jep";


        return view('vindParking.index', compact('parkings', 'lat', 'Lng', 'zoom', 'nofooter'));
    }






    public function mindervalidenstart()
    {
        $plaatsen = count(DB::table('voorbehouden_plaatsen')->get());
        $nofooter = "jep";

        return view('mindervaliden.start', compact('plaatsen', 'nofooter'));
    }

    public function mindervaliden(Request $request, $coords = 0)
    {
        $nofooter = "jep";

        //Als er op de kaart geklikt wordt
        if($coords != 0)
        {
            $lat = substr($coords, 0, strpos($coords, ','));
            $Lng = substr($coords, strpos($coords, ',') + 1);


            $mindervalidenplaatsen = DB::table('voorbehouden_plaatsen')
                ->whereBetween('latitude', [$lat - 0.007, $lat + 0.007])
                ->whereBetween('longitude', [$Lng - 0.007, $Lng + 0.007])
                ->get();

            if(count($mindervalidenplaatsen) > 0) {

                $origins_string = "";

                foreach ($mindervalidenplaatsen as $mindervalidenplaats) {
                    $origins_string .= "" . $mindervalidenplaats->latitude . "," . $mindervalidenplaats->longitude . "|";
                }

                $json = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat . ',' . $Lng . '&destinations=' . $origins_string . '&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo');

                $data = json_decode($json);

                $afstanden = $data->rows[0]->elements;

                foreach ($afstanden as $key => $afstand) {
                    $mindervalidenplaatsen[$key]->afstand = $afstand->distance->value;
                }


                uasort($mindervalidenplaatsen, array($this, 'sort_by_order'));

            } else {
                $mindervalidenplaatsen = [];
            }


            return view('mindervaliden.index', compact('mindervalidenplaatsen', 'lat', 'Lng', 'nofooter'));
        }


        //Als er niets werd ingevuld in het zoekveld
        if($request->location == null && $request->coordinates == null)
        {

            return view('mindervaliden.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558'])->with('parkings', [])->with('mindervalidenplaatsen', []);
        }
        //Als men een adres manueel intypt zonder selectie van google adviezen
        else if($request->coordinates == null)
        {
            $searchFor = str_replace(",","+", $request->location);
            $searchFor = str_replace(" ","+", $searchFor);

            $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
            $data = json_decode($json);

            if($data->status == "ZERO_RESULTS") {
                return view('mindervaliden.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558'])->with('parkings', [])->with('mindervalidenplaatsen', []);
            } else {
                $lat = $data->results[0]->geometry->location->lat;
                $Lng = $data->results[0]->geometry->location->lng;
            }

        }
        //Als de coordinaten via google selectie omgezet worden en doorgestuurd
        else
        {
            $lat = substr($request->coordinates, 0, strpos($request->coordinates, ','));
            $Lng = substr($request->coordinates, strpos($request->coordinates, ',') + 1);
        }

        $mindervalidenplaatsen = DB::table('voorbehouden_plaatsen')
            ->whereBetween('latitude', [$lat - 0.007, $lat + 0.007])
            ->whereBetween('longitude', [$Lng - 0.007, $Lng + 0.007])
            ->get();

        if(count($mindervalidenplaatsen) > 0)
        {

            $origins_string = "";

            foreach ($mindervalidenplaatsen as $mindervalidenplaats) {
                $origins_string .= "" . $mindervalidenplaats->latitude . "," . $mindervalidenplaats->longitude . "|";
            }

            $json = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $lat . ',' . $Lng . '&destinations=' . $origins_string . '&mode=walking&language=nl-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo');

            $data = json_decode($json);

            $afstanden = $data->rows[0]->elements;

            foreach ($afstanden as $key => $afstand) {
                $mindervalidenplaatsen[$key]->afstand = $afstand->distance->value;
            }


            uasort($mindervalidenplaatsen, array($this, 'sort_by_order'));

        } else {
            $mindervalidenplaatsen = [];
        }


        return view('mindervaliden.index', compact('mindervalidenplaatsen', 'lat', 'Lng', 'nofooter'));
    }

    private static function sort_by_order ($a, $b)
    {
        return $a->afstand - $b->afstand;
    }

    function multiexplode ($delimiters,$string) {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    //Wordt gebruikt om eenvoudig alle data vd "Parkings" tabel te inserten
    public function enterData()
    {

        $json = file_get_contents("C:/parkeerplaatsenpersonenmeteenbeperking.geojson");
        $data = json_decode($json);

            foreach($data->features as $parking)
            {
                $coordinaten = $parking->geometry->coordinates;
                $lat = $coordinaten[1];
                $long = $coordinaten[0];

                $eigenschappen = $parking->properties;

                DB::insert("insert into voorbehouden_plaatsen(ID_WESTKANS, ADRES_STRAAT, ADRES_NR, POSTCODE, PARKING_BREEDTE_DATA, PARKING_LENGTE_DATA, GEMEENTE, DEELGEMEENTE, longitude, latitude) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ,
                    [$eigenschappen->xKey,
                        $eigenschappen->gmStraat,
                        0,
                        9000,
                        $eigenschappen->Afmeting2,
                        $eigenschappen->Afmeting1,
                        $eigenschappen->voGemeente,
                        '',
                        $long,
                        $lat]);
            }
    }



//        $xml=simplexml_load_file("https://datatank.stad.gent/4/mobiliteit/parkeerplaatsenpersonenmeteenbeperking.kml") or die("Error: Cannot create object");
//        $placemarks = $xml->Document->Folder->Placemark;
//
//        for ($i = 0; $i < sizeof($placemarks); $i++)
//        {
//            $coordinaten = explode(",", $placemarks[$i]->Point->coordinates);
//
//            $lat = $coordinaten[1];
//            $long = $coordinaten[0];
//
//            $parking = ($placemarks[$i]->ExtendedData->SchemaData->SimpleData);
//
//
//           DB::insert("insert into mindervaliden(latitude, longitude, adres, breedte, lengte)
//          values(?, ?, ?, ?, ?)" ,
//                [$lat,
//                    $long,
//                    $parking[21] . " 9000" . " " . $parking[28],
//                    $parking[7],
//                    $parking[6]
//                ]);
//
//        }

//        $json = file_get_contents("http://web10.weopendata.com/measurements/vpp.json");
//        $data = json_decode($json);
//
//        foreach($data as $parking) {
//
//            DB::insert("insert into mindervaliden(latitude, longitude, adres, breedte, lengte, ondergrond, orientatie, breedte_uitstapzone, lengte_uitstapzone, afbeelding)
//values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ,
//                [$parking->LAT,
//                    $parking->LON,
//                    $parking->ADRES_STRAAT . " " . $parking->ADRES_NR . " " . $parking->POSTCODE . " " . $parking->GEMEENTE,
//                    $parking->PARKING_BREEDTE_DATA,
//                    $parking->PARKING_LENGTE_DATA,
//                    $parking->PARKING_ONDERGROND_MATERIAAL,
//                    $parking->PARKING_ORIENTATIE,
//                    $parking->PARKING_BREEDTE_UITSTAPZONE_TEKST,
//                    $parking->PARKING_LENGTE_UITSTAPZONE_TEKST,
//                    $parking->URL_PICTURE_MAIN
//                ]);
//        }

//        $json = file_get_contents("https://datatank.stad.gent/4/mobiliteit/parkeerplaatsenpersonenmeteenbeperking.geojson");
//        $data = json_decode($json);
//
//        foreach($data->coordinates as $parking) {

//         $opzoeking =   file_get_contents("http://maps.google.com/maps/api/geocode/json?latlng=" . $parking[1] . ",".$parking[0]."?key=AIzaSyAmbwCWgCrmQuWTxgRliw1dofKR-n0zWkA");
//            $data = json_decode($opzoeking);
//
//            $array =  explode(",",$data->results[0]->formatted_address);

//            $straat = "";
//            $nummer = "";
//            $postcode = "";
//            $gemeente = "";
//
//            if(isset($data->results[0]->address_components[1]->long_name))
//            {
//                $straat = $data->results[0]->address_components[1]->long_name;
//            }
//
//            if(isset($data->results[0]->address_components[0]->long_name))
//            {
//                $nummer = $data->results[0]->address_components[0]->long_name;
//            }
//
//            if(isset($data->results[0]->address_components[6]->long_name))
//            {
//                $postcode = $data->results[0]->address_components[6]->long_name;
//            }
//
//            if(isset($data->results[0]->address_components[2]->long_name))
//            {
//                $gemeente = $data->results[0]->address_components[2]->long_name;
//            }

//        DB::insert("insert into voorbehouden_plaatsen(GEMEENTE, longitude, latitude) values(?, ?, ?)" ,
//                ['Gent',
//                    $parking[0],
//                    $parking[1]]);
//        }


//        $json = file_get_contents("D:/parkeerplaatsen.json");
//        $data = json_decode($json);
//
//        dd($data);
//
//        foreach($data->data as $parking) {
//
//
//            $adres_aray = $this->multiexplode(array(","," "), $parking->adres);
//
//
//
//            DB::insert("insert into voorbehouden_plaatsen(ADRES_STRAAT, ADRES_NR, POSTCODE, GEMEENTE, DEELGEMEENTE, longitude, latitude) values(?, ?, ?, ?, ?, ?, ?)" ,
//                [$adres_aray[0],
//                    $adres_aray[1],
//                    $adres_aray[3],
//                    'Antwerpen',
//                    $adres_aray[3],
//                    $parking->point_lng,
//                    $parking->point_lat]);
//        }


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
