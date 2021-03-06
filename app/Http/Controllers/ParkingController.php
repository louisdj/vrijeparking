<?php

namespace App\Http\Controllers;

use App\Betaalmogelijkheid;
use App\mindervalidenplaats;
use App\Openingsuren;
use App\Parking;
use App\Stad;
use App\Tarief;
use App\Zone;
use App\Zone_gebied;
use App\User;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class ParkingController extends Controller
{
    public function suggestie(Request $request)
    {
        $this->validate($request, [
            'adres' => 'required|unique:parkings'
        ]);

        $parking = new Parking();

        $parking->naam = $request->naam;

        $parking->latitude = explode(",",$request->coordinaten)[0];
        $parking->longitude = explode(",",$request->coordinaten)[1];

        $parking->adres = $request->adres;
        $parking->stad = $request->stad;
        $parking->totaal_plaatsen = $request->plaatsen;

        $parking->voorstel = 1;
        $parking->voorstel_vernoeming = $request->vernoeming;

        $parking->save();
    }

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
        $parkings = Parking::all()->where('stad', strtolower($stad))->where('parkandride', 0);
        $parkandrides =  Parking::all()->where('stad', strtolower($stad))->where('parkandride', 1);
        $stad = Stad::where('stad', $stad)->first();

        return view('templates.stad_template', compact('stad', 'parkings', 'parkandrides'));
    }

    public function overzicht_steden()
    {
        $offline_steden = Stad::where('live_data', 0)->orderBy('stad', 'asc')->get();

        return view('parking.overzicht_steden', compact('offline_steden'));
    }

    public function parking_zonder_stad($parkingnaam)
    {
        return $this->parking(null, $parkingnaam);
    }

    public function parking($stad, $parkingnaam)
    {
        if($stad) {
            $parking = Parking::where('stad', $stad)
                ->where('naam', $parkingnaam)->first();
        } else {
            //legacy ondersteuning
            $parking = Parking::where('naam', $parkingnaam)->first();
        }

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

    public function vindparking() {

        $lat = "50.7755478";
        $Lng = "3.6038558";
        $users = User::all()->take(10);

        return view('vindParking.index', ['parkings' => [],'lat' => $lat, 'Lng' => $Lng, 'start' => "ja", 'zoom' => 9, 'nofooter' => 'true', 'zones' => [], 'users' => $users]);
    }

    public function vindparkingpost(Request $request, $coords = 0)
    {
        $zoom = 15;
        $parkings = [];
        $zones = [];
        $gebieden_array = [];

        $nofooter = "jep";
        $stad = "nogniets";
        $users = User::all()->take(10);

        //Als er op de kaart geklikt wordt
        if($coords != 0)
        {
            $lat = substr($coords, 0, strpos($coords, ','));
            $Lng = substr($coords, strpos($coords, ',') + 1);


            $parkings = DB::table('parkings')
                ->whereBetween('latitude', [$lat - 0.025, $lat + 0.025])
                ->whereBetween('longitude', [$Lng - 0.025, $Lng + 0.025])
                ->get();

            if(count($parkings) > 0) {

                $origins_string = "";

                foreach ($parkings as $parking) {
                    $origins_string .= "" . $parking->latitude . "," . $parking->longitude . "|";
                    $stad = $parking->stad;
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

            }

//            $zone_stad = Stad::where('stad', $stad)->get()[0];
            $zones = Zone::where('stad', $stad)->get();


            foreach ($zones as $zone) {
                array_push($gebieden_array, count(DB::table('zone_gebieden')->distinct()->select('gebied')->where('zone_id', $zone->id)->get()));
            }

            return view('vindParking.index', compact('parkings', 'zoom', 'lat', 'Lng', 'zones', 'gebieden_array', 'nofooter', 'users'));
        }


        //Als er niets werd ingevuld in het zoekveld
        if($request->location == null && $request->coordinates == null)
        {
            return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558', 'users' => $users])->with('parkings', [])->with('mindervalidenplaatsen', []);
        }
        //Als men een adres manueel intypt zonder selectie van google adviezen
        else if($request->coordinates == null)
        {
            $searchFor = str_replace(",","+", $request->location);
            $searchFor = str_replace(" ","+", $searchFor);

            $json = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $searchFor);
            $data = json_decode($json);

            if($data->status == "ZERO_RESULTS") {
                return view('vindParking.index', ['mapCenter' => "50.7755478,3.6038558",'zoom' => 8, 'lat' => '50.7755478', 'Lng' => '3.6038558', 'users' => $users])->with('parkings', [])->with('mindervalidenplaatsen', []);
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
            ->whereBetween('latitude', [$lat - 0.025, $lat + 0.025])
            ->whereBetween('longitude', [$Lng - 0.025, $Lng + 0.025])
            ->get();

        if(count($parkings) > 0)
        {

            $origins_string = "";

            foreach ($parkings as $parking) {
                $origins_string .= "" . $parking->latitude . "," . $parking->longitude . "|";
                $stad = $parking->stad;
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

        }

        $zones = Zone::where('stad', $stad)->get();

        foreach ($zones as $zone) {
            array_push($gebieden_array, count(DB::table('zone_gebieden')->distinct()->select('gebied')->where('zone_id', $zone->id)->get()));
        }


        return view('vindParking.index', compact('parkings', 'lat', 'Lng', 'zoom', 'nofooter', 'zones', 'gebieden_array', 'users'));
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
        $content = file_get_contents("https://datatank.stad.gent/4/mobiliteit/bezettingparkeergaragesnmbs.json");
        $gsp_data = json_decode($content);

        Parking::where('naam', "Gent Sint-Pieters")
            ->update(['beschikbare_plaatsen' => $gsp_data[0]->parkingStatus->availableCapacity]);

        dd($gsp_data[0]->parkingStatus->availableCapacity);



//        $xml=simplexml_load_file("http://data.its.be/storage/f/2016-12-01T16%3A10%3A11.824Z/hasselt-datex-v1-1.xml") or die("Error: Cannot create object");
        $xml = simplexml_load_file("C:/Users/robbert/Desktop/datasets/hasselt.xml") or die("Error: Cannot create object");
//        $placemarks = $xml->Document->Folder->Placemark;

        foreach($xml->children() as $parking) {

            $bestaat_reeds = Stad::where('stad', strtolower(htmlentities($parking->parkingSiteAddress->contactDetailsCity->values->value)))->first();
            $gratis = 0;
            $continu_open = 0;

            if(htmlentities($parking->tariffsAndPayment->freeOfCharge) == "true") {
                $gratis = 1;
            }

            if(htmlentities($parking->openingTimes->available24hours) == "true") {
                $continu_open = 1;
            }

            if(count($bestaat_reeds) == 0) {
                $stad = new Stad();
                $stad->stad = strtolower(htmlentities($parking->parkingSiteAddress->contactDetailsCity->values->value));
                $stad->save();
            }

            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude, totaal_plaatsen, telefoon, email, gratis, continu_open) values(?, ?, ?, ?, ?, ?, ?, ?,?,?)" ,
                [htmlentities($parking->parkingName->values->value),
                    strtolower(htmlentities($parking->parkingSiteAddress->contactDetailsCity->values->value)),
                    str_replace('fr:','', str_replace('nl:','',str_replace('nl:','',htmlentities($parking->parkingSiteAddress->contactDetailsStreet)))) . " " . htmlentities($parking->parkingSiteAddress->contactDetailsHouseNumber),
                    htmlentities($parking->parkingLocation->pointByCoordinates->pointCoordinates->latitude),
                    htmlentities($parking->parkingLocation->pointByCoordinates->pointCoordinates->longitude),
                    htmlentities($parking->parkingNumberOfSpaces),
                    htmlentities($parking->parkingSiteAddress->contactDetailsTelephoneNumber),
                    htmlentities($parking->parkingSiteAddress->contactDetailsEMail),
                    $gratis,
                    $continu_open]);
        }

        //naam
        //htmlentities($xml->parkingSite->parkingName->values->value);

        //plaatsen
//        dd(htmlentities($xml->parkingSite->parkingNumberOfSpaces));

        //coordinaten
//        dd(htmlentities($xml->parkingSite->parkingLocation->pointByCoordinates->pointCoordinates->latitude));
//        dd(htmlentities($xml->parkingSite->parkingLocation->pointByCoordinates->pointCoordinates->longitude));

        //straat
//        dd(str_replace('nl:','',htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsStreet)));

        //nummer
//        dd(htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsHouseNumber));

        //postcode
//        dd(htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsPostcode));

        //stad
//        htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsCity->values->value);

        //telefoonnummer
//        dd(htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsTelephoneNumber));

        //email
//        dd(htmlentities($xml->parkingSite->parkingSiteAddress->contactDetailsEMail));



        }



    public function toevoegen()
    {
        return view('parkeerzones.gebieden_toevoegen');
    }

    public function toevoegen2()
    {
        return view('parkeerzones.gebieden_toevoegen2');
    }

    public function toevoegenPost(Request $request)
    {
//        dd($request->coordinaten);

        $unfilteredArray  = $request->coordinaten;
        $pieces = explode("),\r\n", $unfilteredArray);

//        dd(str_replace("new google.maps.LatLng(", "", $pieces[0]));

        foreach($pieces as $piece)
        {
            $zone_gebied = new Zone_gebied();

            $zone_gebied->zone_id = $request->zone_id;
            $zone_gebied->gebied = $request->gebied;
            $zone_gebied->coordinaten = str_replace("new google.maps.LatLng(", "", $piece);

            $zone_gebied->save();
        }



        return view('parkeerzones.gebieden_toevoegen');
    }

    public function toevoegenPost2(Request $request)
    {
//        dd($request->coordinaten);

        $unfilteredArray  = $request->coordinaten;
        $pieces = explode("],", $unfilteredArray);

//        dd(str_replace("new google.maps.LatLng(", "", $pieces[0]));

        foreach($pieces as $piece)
        {
            $zone_gebied = new Zone_gebied();

            $zone_gebied->zone_id = $request->zone_id;
            $zone_gebied->gebied = $request->gebied;

            $coordinaten_verkeerd = str_replace("[", "", $piece);
            $array = explode(",", $coordinaten_verkeerd);
//            $lat = $array[0];
//            $long = $array[1];

            $zone_gebied->coordinaten = $array[1] . "," . $array[0];

            $zone_gebied->save();
        }



        return view('parkeerzones.gebieden_toevoegen2');
    }






//
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
//        }

//        $parkings = [[51.153198,3.236547,'P4',24,0,'mobiliteit@oostkamp.be','+32 5 081 98 80'],[50.990475,3.33114,'Loskaai',35,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.867563,3.810602,'P5',50,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.732194,5.697756,'P1',40,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.353173,5.455016,'P3',100,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[50.4025299362754,4.52825546264648,'Grand rue',20,0,'urbanisme@chatelet.be',''],[50.8663131488207,4.24496322870254,'Station',40,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.611092,5.51049,'P10',40,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[51.001198,4.986276,'Markt',57,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.197278,4.547067,'P2',40,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[50.024305,5.37335,'P3',30,0,'benedicte.pecquet@saint-hubert.be','+32 61 26 09 84'],[50.7162874292191,4.60579544305801,'Parking des Carmes',119,0,'parkings@wavre.be',''],[50.8815628219342,3.43309879302978,'Jeugdcentrum',95,0,'verkeer@waregem.be',''],[50.833722,5.101215,'OCMW',50,0,'ludo.devos@zoutleeuw.be','+32 11 78 49 29'],[49.680474,5.809544,'Parking Gare 2',496,0,'info@b-parking.be',''],[50.413832,4.321222,'P4',25,0,'travaux@villedefontaine.be','+32 71 54 81 31'],[50.8823954133324,3.42673659324646,'Waregem Expo',420,0,'verkeer@waregem.be',''],[50.8495610899943,2.8743115067482,'Tulpenlaan',200,0,'parkeren@ieper.be','+32 5 745 18 44'],[51.209371,3.444146,'Belfius',59,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.9088916860194,4.51330751180649,'Bibliotheek',150,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[51.172741,4.447655,'Krijgsbaan',100,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[50.395984,4.700048,'P4',11,0,'accueil@fosses-la-ville.be','+32 71 26 60 55&#160;'],[50.807777,5.35795,'P6: Fruitveiling',100,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.707878,2.885402,'P10',13,0,'info@polcom.be','+32 56 55 00 55'],[50.845487,3.606337,'de Woeker',40,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.04756,4.316954,'P3',30,0,'accueil@ville-de-chimay.be','+32 60 21 02 92'],[50.836587,3.319319,'Parking Gavers West',200,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.132388,5.790615,'P3',6,0,'vinciane.hazee@houffalize.be','+32 61 28 00 64'],[51.150366,2.722765,'Dienstweg havengeul',90,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.660497964119,5.52034556865692,'Eglise Sainte-Marie',60,0,'urbanisme@ans-commune.be','+32 4 247 72 43'],[50.025943,5.36933,'P2',40,0,'benedicte.pecquet@saint-hubert.be','+32 61 26 09 84'],[51.071985,2.658589,'Daniel Dehaenelaan',25,0,'info@parkeren.be','+32 16 23 56 09'],[50.731484,5.695971,'P3',20,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[51.096696,4.138568,'parking bibliotheek (Achterthof)',30,0,'gemeente@hamme.be','+32 5 247 55 11'],[50.8452379063601,4.35443972714801,'Royal Windsor',103,0,'info.royalwindsor@warwickhotels.com',''],[50.159027,5.223403,'Parking place roi Albert Ier',20,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[51.040695,5.172885,'Oud gemeentehuis',25,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.141567,2.702488,'Albert I laan',130,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.523488,5.242674,'Avenue Godin-Parnajon',117,0,'info@huy.be','+32 85 24 17 00'],[51.188675,3.565201,'P6',100,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.6668383206046,4.61343973875046,'Leclercq',500,0,'walter.leonardva@skynet.be',''],[50.4600722251993,3.9561939239502,'Rue P.-J Dumenil',31,0,'sebastien.gremeaux@ville.mons.be',''],[50.816893,3.332266,'Bekaertstraat',27,0,'zwevegem@parkeren.be','+32 5 120 61 85'],[50.9647246811156,5.48498868942261,'Limburghal gratis parking',430,0,'info@limburghal.be',''],[51.1035556263891,3.98714661598206,'Den Dam',80,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.423662,6.025037,'Arsiliers',30,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.180516,3.563344,'P7',90,0,'eeklo@parkeren.be','+32 16 23 56 09'],[49.699345,5.311851,'P2',25,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[51.1071967204215,3.98695349693298,'Voorkant station',43,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[51.087075,5.219485,'Kerk ',40,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.005907,5.723156,'P18',200,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.6344154710792,3.77245455980301,'Place de la lib&#233;ration',30,0,'mobilite@ath.be',''],[50.6742839969709,4.57433581352234,'Parking Gare Droits de l&#39;Homme',430,0,'info@b-parking.be',''],[50.8511257987008,2.88665771484375,'Grote markt',124,0,'parkeren@ieper.be','+32 5 745 18 44'],[50.720611,4.529914,'P1',22,0,'mobilite@rixensart.be','+32 2 634 35 64'],[51.3164959773672,4.4263020157814,'Kerkplein',34,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.55204,3.805059,'P5',25,0,'m.maquet@chievres.be','+32 68 65 68 20'],[50.600454,3.621615,'P3',30,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[50.992729,5.050416,'Station',650,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[50.514206,5.237065,'Rampe d&#39;Orval',236,0,'info@huy.be','+32 85 24 17 00'],[50.72249,4.514963,'P5',70,0,'mobilite@rixensart.be','+32 2 634 35 64'],[50.227045,5.341846,'Place Toucr&#233;e',20,0,'adl@marche.be','+32 84 32 70 78'],[50.7778873016179,5.46745240688324,'De Motten',251,0,'info@parkeren.be','+32 16 23 56 09'],[51.080233699967,4.71799492835999,'P7: Molenstraat',100,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.731525,5.700328,'P2',20,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[51.1977880029889,4.43242281675339,'Posthoflei',265,0,'info@b-parking.be','+32 2 525 94 35'],[50.6657230673964,4.61175799369812,'Magritte',50,0,'walter.leonardva@skynet.be',''],[51.1062536338577,3.98492574691772,'station westkant',280,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.7179041541724,4.6067476272583,'Avenue des M&#233;sanges',54,0,'parkings@wavre.be',''],[50.7460155899872,3.21479380130768,'Rue de Bruxelles',97,0,'michel.deweerdt@mouscron.be',''],[51.32785300214,4.94975388050079,'P3: Patersstraat',45,0,'mobiliteit@turnhout.be','+32 14 44 33 93'],[50.7723612873828,3.87168288230896,'Vooruitzichtstraat',82,0,'mobiliteit@geraardsbergen.be',''],[50.854041,3.313784,'CC Het Spoor',80,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.052513,4.496749,'Rue de la Ville',25,0,'info@couvin.be','+32 60 34 01 10'],[50.4769123674356,4.17971849441528,'P6: Point d&#39;Eau',120,0,'fabian.bertoni@q-park.com',''],[50.517342,5.235474,'Chauss&#233;e Napol&#233;on',50,0,'info@huy.be','+32 85 24 17 00'],[50.8086195603269,4.94074165821075,'Alexianen',109,0,'parkeershop.tienen@apcoa.be',''],[50.4039926573251,4.51682925224304,'P+M Ouest',100,0,'urbanisme@chatelet.be',''],[50.675927,5.081554,'P5',45,0,'info.be@parkindigo.be','+32 19 80 00 00'],[51.064404,3.101971,'Markt',61,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.9853830146813,3.52604269981384,'Markt',50,0,'shop.deinze@q-park.be',''],[51.11275,2.634288,'P5',250,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[51.107573949659,3.98784935474396,'Station oostkant',68,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.631494,5.538688,'P4',33,0,'info@saint-nicolas.be','+32 4 252 98 90'],[50.7176290431495,4.39646512269974,'Parking de l&#39;eglise',50,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[51.1253844213133,4.57335948944092,'De Mol',423,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.749038,5.078948,'Station',500,0,'info@parkeren.be','+32 16 23 56 09'],[50.9485506584314,3.1260958313942,'De Coninckplein',119,0,'dipod@roeselare.be','+32 51 22 72 11'],[50.447814,3.820695,'R&#233;sidence du Val d&#39;Haine',20,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.111758,4.9495,'P4',20,0,'contact@beauraing.be','+32 82 71 00 10'],[50.8440877553337,4.26224201917648,'Oudesmitsestraat',100,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.4093175217975,4.17322754859924,'Parking de la Gare',160,0,'affaires.economiques@binche.be',''],[51.351497,4.640395,'P1',30,0,'kristine.vanbavel@brecht.be','+32 3 660 25 58'],[51.186512,3.006177,'Stationsstraat',100,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[50.4103531555707,3.89251828193665,'Eglise',62,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[50.392862,5.930789,'P5',8,0,'etc@abbayedestavelot.be','+32 80 88 08 78'],[50.8479692329056,2.87919044494629,'Esplanade',150,0,'parkeren@ieper.be','+32 5 745 18 44'],[51.1952869596245,3.21763157844543,'B-parking',500,0,'info@b-parking.be','NA'],[51.1041518288599,3.98711979389191,'Oude Vismijn',36,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.516609,5.238648,'Saint-Remy (cr&#232;che)',10,0,'info@huy.be','+32 85 24 17 00'],[51.089982,4.919294,'De Krekke',44,0,'info@westerlo.be','+32 1 454 75 75'],[50.856306,2.729697,'P11: Jeugdcentrum',55,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.4885385348445,5.09088903665543,'Andenne Arena',100,0,'parkingshop.andenne@besixpark.com','02/540.82.00'],[51.2227439340238,2.92258858680725,'Maria-Hendrikapark',300,0,'stadsbestuur@oostende.be',''],[50.6048233923566,3.38075280189514,'Esplanade de l???Europe',390,0,'info@q-park.be',''],[50.394933,5.932826,'P3',15,0,'etc@abbayedestavelot.be','+32 80 88 08 78'],[51.1665639770118,4.14591193199158,'P5: Zwijgershoek',136,0,'mobiliteit@sint-niklaas.be',''],[50.69462,5.250323,'P6',120,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[51.1914141084835,5.11536419391632,'Keirlandse zillen',180,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.398374,4.694343,'P2',30,0,'accueil@fosses-la-ville.be','+32 71 26 60 55&#160;'],[50.291059,5.091791,'Gare 2',15,0,'contact@ciney.be','+32 83 23 10 24'],[50.7437209497537,3.21253538131714,'Place Picardie',32,0,'michel.deweerdt@mouscron.be',''],[50.9330158548727,5.34535328946151,'Kolonel Dusart',200,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.85482,2.735517,'P9: Stationsplein',41,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[49.566767,5.529903,'P4',32,0,'jean-pol.stevenin@virton.be','+32 63 44 01 64'],[51.2880514495812,4.48883436620235,'Oude baan',59,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[50.004319,5.718098,'P13',20,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.1283033502256,4.57570105791092,'Gasthuisvest',250,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.4539793421903,3.94628047943115,'Square Roosevelt',70,0,'sebastien.gremeaux@ville.mons.be',''],[50.47729813172,4.17978286743164,'P5: Gare du Centre',250,0,'fabian.bertoni@q-park.com',''],[51.208159,3.450369,'Kannunnik Andries',41,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[51.06996,2.666216,'Kaaiplaats 2',100,0,'info@parkeren.be','+32 16 23 56 09'],[50.7309513924977,4.24029886722565,'Sint-Rochusstraat',40,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.260817,4.908635,'P1',150,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.964258445265,5.50629079341888,'P2 Molenvijver',60,0,'info@parkeren.be',''],[51.027335,4.98244,'Sint-Jansplein',70,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.615794,5.504701,'P3',75,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[51.070083,2.661983,'St. Denisplaats',50,0,'info@parkeren.be','+32 16 23 56 09'],[50.8236680429616,3.2609224319458,'Pieter Tacklaan',235,0,'info@b-rail.be','+32 5 628 12 12'],[50.598678,5.509771,'P13',50,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[49.694413,5.413429,'P2',18,0,'commune@chiny.be','+32 61 32 53 53'],[50.5723492292862,4.06738758087158,'P15',300,0,'aleduc@rauwers.be',''],[50.9066289570708,4.46000128984451,'P1 Brucargo Melsbroek',600,0,'discountparking@interparking.com','+32 2 715 21 10'],[50.9665557940334,5.49706399440765,'P8 Station',149,0,'info@parkeren.be',''],[50.575282530438,4.06632542610168,'P13',150,0,'aleduc@rauwers.be',''],[50.994988,3.743344,'Ter Wallen2',170,0,'mobiliteit@merelbeke.be','+32 9 210 33 11'],[50.296856,5.09804,'Walter Soeur',80,0,'contact@ciney.be','+32 83 23 10 24'],[50.565272,3.754805,'P4',35,0,'m.maquet@chievres.be','+32 68 65 68 20'],[50.632107,5.477616,'P4',25,0,'info@grace-hollogne.be','+32 4 224 53 13'],[50.161814,5.219297,'P2',15,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[51.147458,5.597548,'Sporthal',65,0,'http://parkeren.bree.be/mailons','+32 89 84 85 23'],[50.268835,4.906495,'P3',35,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[51.185731,3.566554,'P11',6,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.737319,5.695166,'P7',30,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.4747581744868,4.18029248714447,'P7: Gazom&#232;tre',100,0,'fabian.bertoni@q-park.com',''],[50.629673,5.531494,'P1',20,0,'info@saint-nicolas.be','+32 4 252 98 90'],[50.180748,5.577045,'P4',20,0,'college.echevinal@la-roche-en-ardenne.be','+32 84 41 12 39'],[50.4092782063111,4.16766464710236,'Parking Saint-Paul',80,0,'affaires.economiques@binche.be',''],[51.2247026003435,2.92762041091919,'Konterdamkaai (station)',313,0,'info@b-parking.be',''],[51.1020415070226,3.99377703666687,'Paterskerk',70,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.4504063555393,3.95642995834351,'Av. Fr&#232;re Orban',59,0,'sebastien.gremeaux@ville.mons.be',''],[50.667575293933,5.50807043910027,'Administration Communale',100,0,'urbanisme@ans-commune.be','+32 4 247 72 43'],[50.812221,3.333241,'Theater',50,0,'zwevegem@parkeren.be','+32 5 120 61 85'],[51.063365,5.219154,'Gemeentehuis',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.871151,3.811357,'P6',50,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.7145008153507,4.39598500728607,'Parking de la commune',100,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.8826661715568,4.70419764518738,'Parking Shopping Center',100,0,'infohuis@leuven.be',''],[51.1076716245848,3.9854621887207,'Achterkant station',220,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.516792,5.240401,'Av des Ardennes',137,0,'info@huy.be','+32 85 24 17 00'],[50.799301,5.356945,'P7: College',45,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.2278,5.343713,'Place Albert 1',22,0,'adl@marche.be','+32 84 32 70 78'],[50.9321039125389,5.32971560972953,'Astrid',300,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.8705854395291,5.51929414272308,'Jazz Bilzen plein',108,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[51.070789,2.666147,'Sasstraat',65,0,'info@parkeren.be','+32 16 23 56 09'],[50.4814389252734,4.1924911737442,'P3',36,0,'fabian.bertoni@q-park.com',''],[49.79654,5.07003,'P3',60,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.7432024799001,3.21339905261993,'Metropole',65,0,'michel.deweerdt@mouscron.be',''],[51.157798,2.967874,'Kasteelstraat',20,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[51.265787,4.714186,'P2',52,0,'mobiliteit@zoersel.be','+32 3 298 09 13'],[49.6838808316968,5.81632733345032,'Parking Hotel de Ville',60,0,'administration@arlon.be',''],[50.63071,5.473842,'P3',50,0,'info@grace-hollogne.be','+32 4 224 53 13'],[50.634533,6.031552,'Bushof',74,0,'info@eupen-info.be','+32 87 55 34 50'],[50.595322,5.458578,'P1',120,0,'roland.welliquet@flemalle.be','+32 4 234 89 05'],[50.59804,3.618036,'P6',60,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[50.872461,3.807453,'P1',120,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.8118855607983,5.18263667821884,'Zwembad',169,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.6252619376094,3.78359913825989,'Pont Carr&#233; 2',70,0,'mobilite@ath.be',''],[50.4488122208585,3.95012676715851,'Place Grande P&#234;cherie',25,0,'sebastien.gremeaux@ville.mons.be',''],[49.696867,5.302347,'P6',20,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[50.052377,4.493053,'Jardins des Mayeurs',32,0,'info@couvin.be','+32 60 34 01 10'],[51.3130245882104,3.13434362411499,'Koning Albert I-laan',600,0,'blankenberge@parkeren.be',''],[51.1075781597894,3.99125307798386,'Grote Kaai',232,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[51.066311,4.354317,'Stationsplein',30,0,'info@parkeren.be','+32 1 623 56 09'],[51.1974944247651,3.21456581354141,'Oesterparking',175,0,'cov@interparking.com','+32 50 33 90 30'],[50.6615066720223,5.51001772284508,'Gare',300,0,'urbanisme@ans-commune.be','+32 4 247 72 43'],[51.110099,3.699033,'Parking Brielken',56,0,'gemeentewerken@evergem.be','+32 9 216 05 30'],[51.143701,3.140642,'De Braambeier',44,0,'mobiliteit@zedelgem.be','+32 5 028 82 29'],[51.153626,2.962486,'Centrumparking',113,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.52257,5.24166,'Centre culturel',16,0,'info@huy.be','+32 85 24 17 00'],[50.725078,4.8696,'Parking des Soeurs Grises',29,0,'environnement@jodoigne.be','+32 10 81 99 93'],[50.4115932444807,3.89420002698898,'Saint-Ghislain',20,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[49.700251,5.3109,'P3',15,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[50.225997,5.342563,'Midi/Grange',17,0,'adl@marche.be','+32 84 32 70 78'],[51.171956,5.163366,'Boudewijnlaan',80,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.4820618904353,4.19467985630035,'P1',35,0,'fabian.bertoni@q-park.com',''],[50.351443,5.456066,'P4',50,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[51.088757,5.221104,'Sporthal',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.903467,4.337144,'Parking C',9900,0,'gemeentebestuur@grimbergen.be','+32 2 260 12 11'],[50.056753,4.491889,'Parking SNCB',70,0,'info@couvin.be','+32 60 34 01 10'],[50.6091930074578,3.40081304311752,'Avenue Bozi&#232;re',100,0,'mobilite@tournai.be',''],[50.251354,4.432846,'P3',17,0,'travaux@walcourt.be','+32 71 61 06 27'],[50.93686057,5.319544673,'P+R Alverberg',350,0,'parkeren@hasselt.be','+32 11 23 97 58'],[51.17516,4.835501,'Kerkplein',50,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[51.042684,5.172208,'Sporthal Paal',100,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.032345,5.371226,'Sint-Martinusplein',70,0,'eddy.beerten@houthalen-helchteren.be','+32 1 160 05 70'],[50.818568,3.338002,'Gemeentehuis',225,0,'zwevegem@parkeren.be','+32 5 120 61 85'],[50.6723903912626,4.57013010978699,'Parking Place de la Gare',97,0,'info@b-parking.be',''],[50.91679,3.21436,'Sint-Tillokerk',75,0,'inzegem@parkeren.be','+32 471 51 28 29'],[50.887220,5.652035,'P2',250,0,'mobiliteit@lanaken.be','+32 8 973 07 66'],[50.4319956600853,4.60952371358871,'Gare de Tamines',200,0,'votremail@sambreville.be',''],[50.5813512606041,4.0782156586647,'P2',151,0,'aleduc@rauwers.be',''],[50.598178,3.618239,'P7',52,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[50.226379,5.345892,'Tanneurs',40,0,'adl@marche.be','+32 84 32 70 78'],[51.401204,4.759393,'Gravin Elisabethlaan',38,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.9774480725322,3.53312104940414,'parking station 1',150,0,'shop.deinze@q-park.be',''],[50.91251,3.20612,'CC De Leest',120,0,'inzegem@parkeren.be','+32 471 51 28 29'],[50.93556581,5.34498810800005,'Vildersstraat',299,0,'parkeren@hasselt.be','+32 11 23 97 58'],[51.096801,3.835736,'P2',50,0,'mobiliteit@lochristi.be','+32 9 326 97 70'],[50.8499100290898,4.26194429397583,'CC Westrand',230,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.8618708328413,4.24586445093155,'Stationsstraat',20,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.983692,4.839344,'Demervallei',200,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[50.691431,4.206155,'P1',20,0,'commune.de.tubize@tubize.be','+32 2 391 39 11'],[51.032559365661,2.86158442497253,'Schoolplein',30,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.698473,5.255593,'P3',50,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[51.3217619638979,4.93718504905701,'P8: Diksmuidestraat (zuid)',117,0,'info@b-parking.be','+32 2 525 94 35'],[50.801367,5.353792,'P4: Sporthal',65,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.7139607418606,4.60912942886353,'Parking des Fontaines',72,0,'parkings@wavre.be',''],[50.7737691430437,3.88664960861206,'Vesten',250,0,'mobiliteit@geraardsbergen.be',''],[49.998783,5.71598,'P4',150,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.796467,3.116493,'Ieperstraat',35,0,'info.be@parkindigo.be','+32 5 659 11 20'],[50.724598,4.516471,'P3',40,0,'mobilite@rixensart.be','+32 2 634 35 64'],[50.892753,5.660283,'P1',45,0,'mobiliteit@lanaken.be','+32 8 973 07 66'],[51.250399,5.546129,'Stadhuis',28,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.780708,3.038089,'Sint-Medarduskerk',75,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[49.706871,5.331567,'P7',100,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[50.8666989009326,5.5125617980957,'De Kimpel',140,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[51.05224,5.735168,'P2',80,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[51.029001,5.372958,'P1',150,0,'eddy.beerten@houthalen-helchteren.be','+32 1 160 05 70'],[50.980447,2.749054,'Markt',50,0,'stadsbestuur@lo-reninge.be','+32 58 28 80 20'],[50.6682170497664,4.60967123508453,'Aula',150,0,'walter.leonardva@skynet.be',''],[50.692989,4.041622,'P5',100,0,'environnement@enghien-edingen.be','+32 2 397 14 40'],[51.138505,2.740841,'Robert Orlentpromenade',100,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.4494227083263,4.63167876005173,'Gare de Auvelais nord',30,0,'votremail@sambreville.be',''],[50.407826,4.325058,'P2',30,0,'travaux@villedefontaine.be','+32 71 54 81 31'],[50.4806401524488,4.18364524841309,'P13: Boch',100,0,'fabian.bertoni@q-park.com',''],[50.763904,4.276377,'P3',20,0,'mobiliteit@beersel.be','+32 2 359 17 51'],[51.125227,4.209107,'P6',100,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.0657606275815,3.73415529727936,'Dok noord',900,0,'info.be@parkindigo.be','+32 3 205 65 95'],[50.781678,3.047223,'Stationsstraat',134,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[50.910742,4.195629,'Kerkplein',35,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.587366,3.808731,'P2',120,0,'m.maquet@chievres.be','+32 68 65 68 20'],[50.67615,5.07849,'P4',15,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.296604,5.099946,'Place Monseu',70,0,'contact@ciney.be','+32 83 23 10 24'],[50.7358779702229,4.2383998632431,'Possozplein',155,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.8712146266945,4.26098942756653,'Brusselstraat',40,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[51.155363,2.964994,'Post',68,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.768078,2.997474,'P5',40,0,'info@polcom.be','+32 56 55 00 55'],[51.3250472693882,4.93856906890869,'Diksmuidestraat (noord)',97,0,'info@b-parking.be','+32 2 525 94 35'],[51.072123,2.661844,'Grote Markt',60,0,'info@parkeren.be','+32 16 23 56 09'],[50.990937,3.330094,'Station 2',72,0,'tielt@parkeren.be','+32 51 42 81 40'],[51.236066595384,2.93888032436371,'Oosteroever',109,0,'stadsbestuur@oostende.be',''],[50.5756402375951,4.06618595123291,'P12',50,0,'aleduc@rauwers.be',''],[50.9285316650282,4.41593527793884,'Zenneparking',137,0,'vilvoorde@parkeren.be',''],[51.036976,5.724901,'P6',50,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[51.139004,5.152742,'Veldstraat (Olmen)',40,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.988223,5.03844,'Den Amer',60,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[50.196402,4.543925,'P1',20,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[50.932691512095,4.49686020612717,'De Ribaucourtplein',20,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[49.841662,5.435953,'P3',30,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[49.999209,5.715365,'P6',15,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.105756,2.601391,'P1',100,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.9324758123008,4.42231357097626,' Forges de Clabecq',96,0,'vilvoorde@parkeren.be',''],[50.848159351842,4.26399081945419,'Gemeenteplein Dilbeek',41,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[51.24844,3.276374,'Damme-Zuid',56,0,'info@parkeren.be','+32 16 23 56 09'],[50.9681436010464,5.50381779670715,'Gratis P2 Reinpad',74,0,'mobiliteit@genk.be',''],[50.565429,3.74684,'P3',40,0,'m.maquet@chievres.be','+32 68 65 68 20'],[51.065589,3.106568,'Achterkant station',75,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.613812,5.508246,'P8',30,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.874495349526,5.51782965660095,'Broederschoolplein',78,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.71281,3.830959,'P3',15,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[51.2293335235692,2.92453318834305,'Churchill',410,0,'info@q-park.be',''],[50.80359,5.344096,'P3: Nieuwland',50,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.481911,4.549419,'Rue Emile Vandervelde',55,0,'urbanisme@fleurus.be','+32 71 82 03 79&#160;'],[50.9407716744806,4.04011487960815,'Graanmarkt',40,0,'aalst@parkeren.be','+32 5 379 00 07'],[51.214254,3.443602,'Jeugdlokalen',26,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.560983,4.693638,'P3',15,0,'info@q-park.be','+32 2 711 17 62'],[50.4055351142546,4.52762648463249,'Place Guyot',20,0,'urbanisme@chatelet.be',''],[50.395272,5.932573,'P2',25,0,'etc@abbayedestavelot.be','+32 80 88 08 78'],[51.159021,2.968664,'Kaaistraat',12,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[51.1873476006754,5.12178540229797,'&#39;t Getouw',160,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.521658,5.239746,'Quai Dautrebande',20,0,'info@huy.be','+32 85 24 17 00'],[50.8280935431242,3.25401306152344,'P Haven',250,0,'info@parko.be','+32 5 628 12 12'],[50.8526125620206,2.88085877895355,'Minneplein',200,0,'parkeren@ieper.be','+32 5 745 18 44'],[50.563696,4.692642,'Clos de l&#39;Orneau',25,0,'info@q-park.be','+32 2 711 17 62'],[51.0292385974301,3.75096872448921,'P&amp;R Moscou',15,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[50.226505,5.341205,'H&#244;tel de Ville',30,0,'adl@marche.be','+32 84 32 70 78'],[50.7186615502502,4.61600661277771,'Parking D&#233;sir&#233; Yernaux',73,0,'parkings@wavre.be',''],[50.42743,6.026478,'Malmumdarium',95,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.3085043716714,3.11792314052582,'Jules Soetestadion',100,0,'info@info.be',''],[50.766489,2.997663,'P6',40,0,'info@polcom.be','+32 56 55 00 55'],[51.017415,5.725487,'P1',20,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[50.8178830852105,5.18697112798691,'Abdijstraat',23,0,'info@info.be',''],[51.031446074838,4.09258961677551,'Dakparking Bibliotheek',100,0,'parkeerbeheer@dendermonde.be',''],[50.709414,5.64851,'P1',60,0,'p.deltour@oupeye.be','+32 4 267 07 43'],[50.708111,3.835448,'P8',50,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[51.0194883202258,4.48455691337585,'Parking station',400,0,'info@parkeren.be','+32 476 83 98 86'],[51.151069,2.720604,'Zeedijk',175,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.2982,5.100744,'Centre culturel',70,0,'contact@ciney.be','+32 83 23 10 24'],[50.669678,5.080565,'P8',100,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.000261,5.719553,'P9',40,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.8133345952929,5.18684506416321,'De Bogaart',39,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.422943,6.030777,'Gare',11,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.4497542160252,3.94467115402222,'Place du B&#233;guinage',62,0,'sebastien.gremeaux@ville.mons.be',''],[50.8420661954153,4.27905946969986,'Zwembad',20,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.7428904350913,3.21825921535492,'Roussel',83,0,'michel.deweerdt@mouscron.be',''],[50.4044205853609,4.52327996492386,'Place de l&#39;Hotel de Ville',30,0,'urbanisme@chatelet.be',''],[51.217213,3.452435,'Voetbalstadion',261,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.052227,4.497758,'Ferme Walkens',85,0,'info@couvin.be','+32 60 34 01 10'],[50.873612,3.81553,'P3',200,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[49.692889,5.374425,'P3',20,0,'commune@chiny.be','+32 61 32 53 53'],[50.816893,3.332266,'Sportcentrum',100,0,'zwevegem@parkeren.be','+32 5 120 61 85'],[50.4032214100342,4.44429695606232,'Charleroi Sud P3',100,0,'info@b-parking.be',''],[50.693599,4.202800,'P4',40,0,'commune.de.tubize@tubize.be','+32 2 391 39 11'],[51.18431,3.566966,'P5',30,0,'eeklo@parkeren.be','+32 16 23 56 09'],[51.181019,3.010183,'Sporthal 2',25,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[51.035223,5.726637,'P7',50,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[50.280974,6.122608,'P1',100,0,'kontakt@st.vith.be','+32 80 28 01 30'],[50.981293,2.747499,'Kerkhof',10,0,'stadsbestuur@lo-reninge.be','+32 58 28 80 20'],[50.4556811092036,3.95529806613922,'Place de Bootle',84,0,'sebastien.gremeaux@ville.mons.be',''],[50.457490359384,3.9464521408081,'Parking Gare Bd Charles V',500,0,'info@b-parking.be',''],[50.714882,3.835383,'P4',50,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[51.314359811364,4.42773163318634,'&#39;t Bruggeske',101,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.980347632973,3.5260534286499,'Brielpoort',500,0,'shop.deinze@q-park.be',''],[50.23735,4.235135,'P4',40,0,'delphine.lonnoy@beaumont.be','+32 71 79 70 40'],[51.3202382397888,4.95145440101624,'P2: Muylenberg',145,0,'mobiliteit@turnhout.be','+32 14 44 33 93'],[50.921308,3.211709,'Station',150,0,'inzegem@parkeren.be','+32 471 51 28 29'],[51.3138595053014,4.94373500347137,'AZ campus Sint-Elisabeth',566,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.0736598599366,4.71234887838364,'P6: Brug',30,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[51.350503,4.645629,'P3',30,0,'kristine.vanbavel@brecht.be','+32 3 660 25 58'],[50.478175,4.552465,'Rue de Fleurjoux',100,0,'urbanisme@fleurus.be','+32 71 82 03 79&#160;'],[50.693101,4.205007,'P3',20,0,'commune.de.tubize@tubize.be','+32 2 391 39 11'],[51.16982,4.448237,'???t Parkske',40,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[50.607509,4.1404,'P5',150,0,'lena.fanara@7090.be','+32 67 87 48 59'],[49.567158,5.532182,'P2',50,0,'jean-pol.stevenin@virton.be','+32 63 44 01 64'],[50.44844,3.819978,'Grand&#39;place',60,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.496954,3.606288,'Rue de Saint-Amand',40,0,'environnement@peruwelz.be','+32 69 25 40 42'],[50.842759,3.310172,'Parking Gavers Zuid',350,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.833183,5.103516,'Grote markt',50,0,'ludo.devos@zoutleeuw.be','+32 11 78 49 29'],[50.8794948171425,4.69025552272797,'Sint-Jacobsparking',338,0,'infohuis@leuven.be',''],[51.112032,2.630416,'P4',100,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.426845,6.026952,'Ch&#226;telet',20,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.501441,4.108987,'P1',30,0,'frederic.petre@leroeulx.be','+32 64 31 07 45'],[50.946269,5.114414,'Sportlaan',40,0,'ruimtelijke.ordening@halen.be','+32 13 61 81 30&#160;'],[50.056045,4.491669,'Parking square Courth&#233;oux',7,0,'info@couvin.be','+32 60 34 01 10'],[50.6488210192765,5.55675387382507,'Bas Rhieux',450,0,'info.be@parkindigo.be','+32 3 232 30 42'],[51.270268,4.643927,'P3',50,0,'mobiliteit@zoersel.be','+32 3 298 09 13'],[51.1730539800283,4.14909839630127,'P2: Parking station',508,0,'info@b-parking.be',''],[50.912265,4.194886,'Gildehof',50,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.9774024752026,3.52767080068588,'Sint-Martinuskerk',40,0,'shop.deinze@q-park.be',''],[50.4057333574291,3.89477133750915,'J.Brel-Volders',120,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[50.278123,6.127124,'P4',40,0,'kontakt@st.vith.be','+32 80 28 01 30'],[51.002252,3.326792,'Generaal Maczekplein',130,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.514231,5.238878,'Avenue du Hoyoux',70,0,'info@huy.be','+32 85 24 17 00'],[51.130566,5.452585,'P5: Zuidervest',30,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.5796395593073,4.07216727733612,'P5',87,0,'aleduc@rauwers.be',''],[50.849033501225,4.25771713256836,'d&#39;Arconatisstraat',30,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[51.3121049932883,4.42561537027359,'Marktplein',187,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[51.0334533525672,4.10329699516296,'De Bruynkaai',500,0,'mobiliteit@dendermonde.be',''],[51.15747,2.965405,'Wilgenlaan',21,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.132667,5.789276,'P1',50,0,'vinciane.hazee@houffalize.be','+32 61 28 00 64'],[51.009528,3.889715,'P3',24,0,'wetteren@parkeren.be','+32 9 369 79 88'],[50.4503145734845,3.95798027515411,'Machine &#224; eau',60,0,'sebastien.gremeaux@ville.mons.be',''],[50.731577,4.238205,'Vogelpers',42,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.5996719342131,3.39779019355774,'Parking Prison',100,0,'info@q-park.be',''],[50.8819486588222,3.42882871627808,'Voetbalstadion',194,0,'verkeer@waregem.be',''],[50.906564,4.207881,'Station',150,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.9825969051715,3.52618753910065,'Brielstraat',150,0,'shop.deinze@q-park.be',''],[51.2109028110077,4.25284624099731,'Bosdamlaan',53,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.857946,3.316496,'Station Noord',60,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.7372164770264,4.2392461001873,'De Bres',177,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.2308367517417,5.30302226543426,'P9: Klachtloopstraat',140,0,'juridische.dienst@lommel.be',''],[50.995073,3.745373,'Ter Wallen',100,0,'klacht.merelbeke@apcoa.be','+32 3 233 94 23'],[51.1606130239834,4.14406657218933,'P9: Kokkelbeekplein',82,0,'mobiliteit@sint-niklaas.be',''],[50.470087574365,4.86458837985992,'',60,0,'info@b-parking.be',''],[51.110605,2.61001,'P2',30,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.252248,4.431935,'P2',40,0,'travaux@walcourt.be','+32 71 61 06 27'],[50.4560626848712,3.95701467990875,'Damoiseaux',100,0,'sebastien.gremeaux@ville.mons.be',''],[50.7450867062855,3.21677327156067,'Rue de Courtrai',150,0,'michel.deweerdt@mouscron.be',''],[50.632143,5.475893,'P2',20,0,'info@grace-hollogne.be','+32 4 224 53 13'],[50.027817,5.374647,'P1',40,0,'benedicte.pecquet@saint-hubert.be','+32 61 26 09 84'],[51.003827,3.32689,'Atletiekpiste',33,0,'tielt@parkeren.be','+32 51 42 81 40'],[49.6829298227503,5.81311941146851,'Place L&#233;opold',91,0,'administration@arlon.be',''],[51.09473,5.786386,'P2: Hepperpoort',250,0,'parkeren@maaseik.be','+32 89 56 05 60'],[50.4503966942788,3.95373165607452,'Place March&#233; aux poissons',35,0,'sebastien.gremeaux@ville.mons.be',''],[50.253546,4.429567,'P5',100,0,'travaux@walcourt.be','+32 71 61 06 27'],[50.766392,3.000476,'P7',50,0,'info@polcom.be','+32 56 55 00 55'],[50.5793994051938,4.06812787055969,'P8',14,0,'aleduc@rauwers.be',''],[50.93934519,5.33516049399998,'Gelatine',350,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.763904,4.276377,'P2',35,0,'mobiliteit@beersel.be','+32 2 359 17 51'],[51.08873,5.22461,'Korspelsesteenweg',10,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.711079,3.835383,'P7',20,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[51.0734424573223,4.70774352550507,'P5: Halfstraat',121,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.695741,5.252944,'P5',60,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[50.753625,5.082532,'Bovenpoortstraat',25,0,'info@parkeren.be','+32 16 23 56 09'],[50.524407,5.242813,'Piscine communale',48,0,'info@huy.be','+32 85 24 17 00'],[51.094856,4.135845,'parking Slangstraat (hoek Achterthof)',30,0,'gemeente@hamme.be','+32 5 247 55 11'],[50.4733208532774,4.18810844421387,'P10: Belle-vue',250,0,'fabian.bertoni@q-park.com',''],[50.997898,3.325468,'Collegeparking',142,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.8188845747103,5.1936873793602,'Kerkhof',25,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.226358,5.345527,'Armoiries',20,0,'adl@marche.be','+32 84 32 70 78'],[50.558375,4.68951,'P8',10,0,'info@q-park.be','+32 2 711 17 62'],[50.237665,4.235463,'P3',30,0,'delphine.lonnoy@beaumont.be','+32 71 79 70 40'],[50.610719,4.13562,'P7',100,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.279245,6.127096,'P2',20,0,'kontakt@st.vith.be','+32 80 28 01 30'],[50.932807,5.366218,'Grenslandhallen',2785,0,'kurt.ceyssens@sportpaleisgroep.be','+32 474 77 55 16'],[50.002914,5.715882,'P11',80,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.927649,4.366186,'Ter Wilgen',120,0,'gemeentebestuur@grimbergen.be','+32 2 260 12 11'],[51.0323804758657,3.7590354681015,'P&amp;R Gentbrugge - Brusselsesteenweg',200,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[50.196664,4.539781,'P4',65,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[50.8502997678037,4.37041373528393,'Scailquin',500,0,'parkingscailquin@skynet.be',''],[50.05275,4.493575,'Place G&#233;n&#233;ral Piron',60,0,'info@couvin.be','+32 60 34 01 10'],[50.237791,4.238868,'P1',20,0,'delphine.lonnoy@beaumont.be','+32 71 79 70 40'],[50.941667,5.164939,'Guldensporenlaan',30,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.7181894527867,4.61074411869049,'Parking de l&#39;escaille',10,0,'parkings@wavre.be',''],[50.8330607181511,4.19308662414551,'Caerenbergveld',25,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.630701,6.028238,'City',95,0,'info@eupen-info.be','+32 87 55 34 50'],[50.353478,5.457448,'P6',40,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[51.1844340861175,5.11713176965714,'Sint-Pieter',48,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.9310885806037,4.41896080970764,'Slachthuisplein',72,0,'vilvoorde@parkeren.be',''],[50.9408478894552,4.04536722090143,'Molendries',27,0,'parkingmolendries@gmail.com','NA'],[50.8544799078173,4.36804226291439,'Bota',1,0,'mobiliteit@gob.brussels',''],[50.741977,3.600312,'P1',100,0,'parkeren.ronse@parkindigo.be','+32 55 60 55 28'],[50.841648,3.599452,'Minderbroedersstraat',160,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[51.107549,3.701815,'Parking station',24,0,'gemeentewerken@evergem.be','+32 9 216 05 30'],[50.780708,3.038089,'Magdalenastraat',28,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[49.564291,5.530988,'P3',36,0,'jean-pol.stevenin@virton.be','+32 63 44 01 64'],[50.630967,5.535439,'P3',10,0,'info@saint-nicolas.be','+32 4 252 98 90'],[50.424751,6.033667,'Anc. Brasserie',43,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.72639,2.879804,'P11',50,0,'info@polcom.be','+32 56 55 00 55'],[50.001944,5.719045,'P10',100,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.126166,4.216952,'P11',25,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.056405,4.491631,'Parking communal',50,0,'info@couvin.be','+32 60 34 01 10'],[51.209482,3.454545,'Begraafplaats',45,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.161485,5.222151,'Parking de l&#39;Eglise',31,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[50.342121,4.279826,'P5',20,0,'secretariat@thuin.be','+32 71 55 94 11'],[50.4149717746155,4.16339457035065,'Parking de la P&#233;pini&#232;re',60,0,'affaires.economiques@binche.be',''],[50.614698,3.393922,'Parking Gare P2',280,0,'info@b-parking.be',''],[50.854231,2.735114,'P10: Station',57,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.7699491759405,3.87673079967499,'Gasthuisstraat',38,0,'mobiliteit@geraardsbergen.be',''],[50.995347,3.328874,'Conventieweg',25,0,'tielt@parkeren.be','+32 51 42 81 40'],[51.099513,4.138147,'parking Marktplein',50,0,'gemeente@hamme.be','+32 5 247 55 11'],[50.85139,3.602525,'Station voorzijde',300,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.939474,5.165671,'De markthallen',34,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.673421,5.078128,'P2',30,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.341931,4.286028,'P2',30,0,'secretariat@thuin.be','+32 71 55 94 11'],[50.425319,6.033512,'Haute Vaulx',20,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.481747,4.543938,'Gare',50,0,'urbanisme@fleurus.be','+32 71 82 03 79&#160;'],[50.734597,5.693778,'P5',25,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.855695,2.729084,'P5: Paardenmarkt',63,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.7165625481173,4.40024435520172,'Parking Wellington',150,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.9892562733429,3.52574497461319,'Slachthuisstraat',40,0,'shop.deinze@q-park.be',''],[51.211054,3.447219,'Van Mullem',46,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.858894,2.726126,'P12: Maeke-Blyde',52,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[51.209986,3.44277,'Sint-Annezwembad',109,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.780808,3.040702,'Duivenstraat',58,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[51.143288,2.706337,'Franslaan',500,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.197743,4.546163,'P3',20,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[51.2136247915891,4.25001114606857,'Donkvijverstraat',39,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.986641,4.469619,'P3',80,0,'verkeer@zemst.be','+32 1 562 71 35'],[50.193591,4.540585,'P6',50,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[50.8368907742239,4.03247058391571,'Mallaardstraat',25,0,'ninove@parkeren.be','054 50 23 55'],[51.169976,5.170663,'Gustaaf Woutersstraat',30,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.253074,4.431793,'P4',20,0,'travaux@walcourt.be','+32 71 61 06 27'],[50.9113253124089,4.51233386993408,'Hamdreef',60,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[50.5730859786046,4.06569376587868,'P14',50,0,'aleduc@rauwers.be',''],[50.7163434720886,4.39620763063431,'Parking de la piscine',80,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.5986912887359,4.32028234004974,'Arbaletriers',50,0,'valerie.heyvaert@nivelles.be',''],[50.9873787794275,5.69649159908295,'Mie Merkenstraat',35,0,'parkeren.maasmechelen.be@parkindigo.be','+32 8 976 70 85'],[50.8038735377298,3.27607154846191,'P P&amp;R Expo',448,0,'info@parko.be','+32 5 628 12 12'],[50.800141,3.115231,'Station',100,0,'info.be@parkindigo.be','+32 5 659 11 20'],[50.779174,4.285694,'Parking Zuid',34,0,'mobiliteit@sint-pieters-leeuw.be','+32 2 371 22 92'],[51.2151671758283,4.26544189453125,'Essendreef',45,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.707694,2.883798,'P9',90,0,'info@polcom.be','+32 56 55 00 55'],[50.279701,6.123831,'P3',60,0,'kontakt@st.vith.be','+32 80 28 01 30'],[50.519292,5.239431,'Place St-S&#233;verin',18,0,'info@huy.be','+32 85 24 17 00'],[50.475843,4.099142,'P4',8,0,'frederic.petre@leroeulx.be','+32 64 31 07 45'],[50.8638676446839,4.32970425085309,'Simonis',132,0,'info@q-park.be','02/711 17 00'],[50.425425,6.029775,'Albert 1er',72,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.471627,3.8163,'Administration communale',75,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.910787,4.194648,'Oudestraat',30,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.047044,4.317442,'P2',100,0,'accueil@ville-de-chimay.be','+32 60 21 02 92'],[50.867939,3.816384,'Station',260,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.6001418195964,4.32460337877274,'Canonniers',30,0,'valerie.heyvaert@nivelles.be',''],[51.16367437892,4.13497924804688,'P8: Kroonmolenplein',54,0,'mobiliteit@sint-niklaas.be',''],[51.2307191825933,5.31134247779846,'P5: Mudakkers',43,0,'juridische.dienst@lommel.be',''],[51.125556,4.21107,'P5',150,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.18151,3.571522,'P9',150,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.9257845275802,4.43465977907181,'Station',306,0,'vilvoorde@parkeren.be',''],[50.353831,5.455711,'P7',45,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[50.99112,4.834105,'Grote Laakweg',50,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[50.00375,5.717282,'P12',20,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.158456,5.221811,'P3',30,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[50.8523366943014,4.33854670767368,'Brunfaut',152,0,'mobilite@molenbeek.irisnet.be',''],[50.938615,5.166262,'P5',58,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[51.116468,2.625382,'P6',40,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.801897,5.342065,'P1: Graaf',30,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.130069,5.792067,'P4',10,0,'vinciane.hazee@houffalize.be','+32 61 28 00 64'],[50.8043312109131,4.93650376796722,'Kazerne+',450,0,'parkeershop.tienen@apcoa.be',''],[50.746813,3.604553,'P4',150,0,'parkeren.ronse@parkindigo.be','+32 55 60 55 28'],[51.008314,3.882797,'P2',20,0,'wetteren@parkeren.be','+32 9 369 79 88'],[51.0227479914267,4.48733031749725,'Zandpoortvest 1',112,0,'ruimtelijkeplanningenmobiliteit@mechelen.be',''],[50.774657936122,3.87968122959137,'Zakkaai',112,0,'mobiliteit@geraardsbergen.be',''],[50.7144906253425,4.60210204124451,'Parking de la Sucrerie',190,0,'parkings@wavre.be',''],[50.820889183754,5.18918931484222,'Veemarkt',500,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.7180943534416,4.6078097820282,'Pont des Amours',13,0,'parkings@wavre.be',''],[51.156563,2.966852,'Markt',33,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.872227303155,5.52025973796844,'Kloosterwal',42,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.948588,5.111493,'Generaal De Wittestraat',34,0,'ruimtelijke.ordening@halen.be','+32 13 61 81 30&#160;'],[50.980018,4.970013,'Boemelke ',138,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[51.3230392381768,4.5212334394455,'Kerkedreef',70,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[51.0751243517087,4.71739411354065,'P3: Bib',25,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[51.06443,3.100265,'Ravenhofstraat',40,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.9223230252269,5.34090320128439,'Spoorviaduct/Ijzerweg',200,0,'parkeren@hasselt.be','+32 11 23 97 58'],[49.797063,5.066936,'P1',30,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.674711,5.082925,'P6',65,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.8914971036866,3.42294931411743,'Station West',364,0,'verkeer@waregem.be',''],[50.726176,4.867705,'Rendanges',27,0,'environnement@jodoigne.be','+32 10 81 99 93'],[51.0316957241972,2.864910364151,'Bibliotheek/CC',12,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.342335,4.287721,'P1',50,0,'secretariat@thuin.be','+32 71 55 94 11'],[51.131146,2.754394,'Parking stad',50,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.618221,5.509736,'P6',50,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.854432,3.60406,'Station achterzijde',330,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.669461,5.077137,'P9',30,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.407894,4.326313,'P1',60,0,'travaux@villedefontaine.be','+32 71 54 81 31'],[50.22522,5.343841,'Rue Neuve',45,0,'adl@marche.be','+32 84 32 70 78'],[50.620362,5.940478,'P2',10,0,'jonathan.jourdan@ville-limbourg.be','+32 87 76 04 22'],[51.128733,4.208994,'P2',40,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.97896,4.97536,'Stadhuis',75,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[49.843076,5.432239,'P9',100,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[51.1876115395203,5.11676967144012,'Rondplein',130,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.586079,3.805842,'P1',10,0,'m.maquet@chievres.be','+32 68 65 68 20'],[49.839955,5.432409,'P6',44,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[50.4771308537886,4.20162677764893,'P11',160,0,'fabian.bertoni@q-park.com',''],[50.426184,6.021938,'La Warche',50,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.0760411100808,3.67857456207275,'P&amp;R Mariakerke',25,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[51.159271,4.662734,'P5',100,0,'vergunningen@nijlen.be','+32 3 410 02 11'],[50.4450416370643,4.63751256465912,'Rue de ponte Saint Maxenne',50,0,'votremail@sambreville.be',''],[50.8850318238694,4.4715428352356,'Station voorkant',70,0,'parkeren.zaventem.be@parkindigo.com','+32 2 503 68 80'],[50.7122249918401,4.60877001285553,'Parking de l&#39;usine electrique',220,0,'parkings@wavre.be',''],[50.7170788162589,4.61025059223175,'Place Cardinal Mercier',54,0,'parkings@wavre.be',''],[51.3123003104754,3.12052488327026,'Jachthaven',300,0,'info@parkingdb.be','+32 50 42 41 42'],[51.064092,3.106721,'Guido Gezelle',100,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.7717642080893,3.87297570705414,'Parking NMBS',64,0,'mobiliteit@geraardsbergen.be',''],[50.867956,3.816025,'P4',250,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[51.07056,5.220785,'Busstation',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.939322,5.167729,'Veearts Strauvenlaan',27,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.84478,3.30963,'Forestiersstadion',250,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.813098,3.334058,'Kerk',50,0,'zwevegem@parkeren.be','+32 5 120 61 85'],[51.141224,3.137528,'Schoolplein',43,0,'mobiliteit@zedelgem.be','+32 5 028 82 29'],[50.7391965821223,4.24020230770111,'Fons Vandemaelestraat',57,0,'openbare.werken@halle.be','+32 2 365 95 10'],[49.6849081781233,5.81803321838379,'Place des Chasseurs Ardennais',140,0,'administration@arlon.be',''],[50.8479048800936,2.87745237350464,'Stationsplein',104,0,'parkeren@ieper.be','+32 5 745 18 44'],[51.158878,2.975874,'Oostmolen',15,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.608434,4.450344,'Parking de la rue de la Station',110,0,'info@genappe.be','+32 67 79 42 72'],[50.857852,3.317533,'Station Zuid',250,0,'stad@harelbeke.be','+32 5 673 33 11'],[50.292225,5.09106,'Gare 1',25,0,'contact@ciney.be','+32 83 23 10 24'],[50.601747,4.135428,'P3',35,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.934593,4.368587,'Fenikshof',50,0,'gemeentebestuur@grimbergen.be','+32 2 260 12 11'],[50.601391419491,4.32496011257172,'Saint-Roche',450,0,'valerie.heyvaert@nivelles.be',''],[51.15528,2.960647,'Heyvaertlaan',24,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[51.127169,4.221725,'P9',40,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.185164,5.577136,'P2',40,0,'college.echevinal@la-roche-en-ardenne.be','+32 84 41 12 39'],[50.7817591932617,5.4698771238327,'Clarissen',75,0,'info@parkeren.be','+32 16 23 56 09'],[50.980118,2.741113,'Lobrug',10,0,'stadsbestuur@lo-reninge.be','+32 58 28 80 20'],[50.446854501687,4.63158220052719,'Rue Hicguet',25,0,'votremail@sambreville.be',''],[50.810466,5.343186,'P5: Stationsplein',100,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[51.251271,5.545385,'Marktplein',74,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.6969333486723,4.40262079238892,'Parking de joli-bois',20,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.9411562364666,4.03269894725611,'De Schreef',39,0,'info@deschreef.be','+32 5 377 49 69'],[50.4873883339697,5.10112971067429,'Place du Chapitre',101,0,'parkingshop.andenne@besixpark.com',''],[50.8245750878469,4.51434284448624,'Markt',120,0,'parkings.tervuren.be@parkindigo.com ','+32 2 430 79 36'],[51.1041484605059,3.99041891098022,'Markt oostkant',36,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[51.139638,5.597725,'Stadsplein',60,0,'http://parkeren.bree.be/mailons','+32 89 84 85 23'],[50.9260762243724,4.4207176566124,'Commanderie',45,0,'vilvoorde@parkeren.be',''],[51.141517,5.600923,'Witte Torenwal',100,0,'http://parkeren.bree.be/mailons','+32 89 84 85 23'],[50.280058,6.125514,'P5',15,0,'kontakt@st.vith.be','+32 80 28 01 30'],[50.9446892138463,3.12698632478714,'Polenplein',104,0,'dipod@roeselare.be','+32 51 22 72 11'],[50.7866075095328,5.45227646827698,'Pliniuspark',545,0,'info@parkeren.be','+32 16 23 56 09'],[51.0322068264606,2.86158978939056,'Heilig Hart plein',8,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.60923,5.508261,'P11',50,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.4104063374376,4.44157719612122,'Yser',300,0,'info@charleroi.be','071 30 54 00'],[51.062988,3.101213,'Deprez',123,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[51.057767,4.368415,'Bel-Air',60,0,'info@parkeren.be','+32 1 623 56 09'],[50.6116491419441,3.38597774505615,'Quai Andre&#239; Sakharov',500,0,'info@q-park.be',''],[50.290312,5.091062,'Gare 3',150,0,'contact@ciney.be','+32 83 23 10 24'],[51.0286424876613,2.86121964454651,'Parking West',40,0,'parkeren.diksmuide.be@parkindigo.com',''],[51.1048204422574,3.98971080780029,'Markt centraal',80,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[51.176485,4.830535,'Molenvest',30,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.520581,5.236713,'Quai de la Batte',17,0,'info@huy.be','+32 85 24 17 00'],[50.947927,5.116557,'Raubrandplein',20,0,'ruimtelijke.ordening@halen.be','+32 13 61 81 30&#160;'],[51.1818314372886,5.11359393596649,'Rivierstraat',157,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[51.151875,2.723736,'Havengeul',90,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.768601,2.999054,'P1',100,0,'info@polcom.be','+32 56 55 00 55'],[51.2249411286274,4.39918220043182,'Scheldekaaien noord',552,0,'info@apcoa.be','+32 3 233 94 23'],[49.793238,5.069495,'P7',15,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.728146,4.873156,'Hall Sportif',94,0,'environnement@jodoigne.be','+32 10 81 99 93'],[50.8515999424917,2.88569748401642,'St. Maartensplein',97,0,'parkeren@ieper.be','+32 5 745 18 44'],[51.070379,2.665923,'Kaaiplaats',65,0,'info@parkeren.be','+32 16 23 56 09'],[50.489637513678,5.09654715657234,'Place des Tilleuls',40,0,'parkingshop.andenne@besixpark.com',''],[51.132282,5.45075,'P3: Preud&#39;hommeplein',50,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.462772,4.378454,'P2',90,0,'cathy.vanthuyne@courcelles.be','+32 7 146 69 70'],[50.6336499092558,3.77200126647949,'Boulevard de Mons',50,0,'mobilite@ath.be',''],[50.9836320065889,3.51832866668701,'Brielmeersen',140,0,'shop.deinze@q-park.be',''],[50.6661531910554,4.6181845664978,'Place Polyvalente',60,0,'walter.leonardva@skynet.be',''],[50.7153907341933,4.6084052324295,'Parking des Carabiniers',138,0,'parkings@wavre.be',''],[50.697845,5.25722,'P4',45,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[50.612066,4.45133,'Parking de la plaine communale',140,0,'info@genappe.be','+32 67 79 42 72'],[50.766985,4.309347,'P1',20,0,'mobiliteit@beersel.be','+32 2 359 17 51'],[51.070655,5.216128,'Zwembad',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.6697810338325,4.62267190217972,'Euler',100,0,'walter.leonardva@skynet.be',''],[50.743334,4.334717,'P5',40,0,'mobiliteit@beersel.be','+32 2 359 17 51'],[51.340565479506,3.28404307365418,'Parking station',250,0,'knokke-heist@parkeren.be','+32 5 034 23 06'],[50.612414,3.397656,'Parking Gare P1',192,0,'info@b-parking.be',''],[50.795047,3.114203,'Badhuis',100,0,'info.be@parkindigo.be','+32 5 659 11 20'],[51.0335090153325,4.47122633457184,'Rode Kruisplein',204,0,'ruimtelijkeplanningenmobiliteit@mechelen.be',''],[51.031166,5.375771,'John Cuppensplein',100,0,'eddy.beerten@houthalen-helchteren.be','+32 1 160 05 70'],[50.6480829039401,5.5566680431366,'L&#233;gia',28,0,'info@illico-park.be',''],[50.512585,5.239354,'Chauss&#233;e des Forges',37,0,'info@huy.be','+32 85 24 17 00'],[50.8740874478336,5.51308482885361,'College',49,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.8393187455029,4.24963295459747,'Kerk',18,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[49.795751,5.071699,'P6',30,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.8337134631124,4.3556496762492,'Louise Village',145,0,'mobiliteit@gob.brussels',''],[51.3251645973747,4.94951784610748,'P11: PP41',45,0,'info@pp41.be','+32 475 47 36 41'],[51.064194,4.363843,'Groen Laan',40,0,'info@parkeren.be','+32 1 623 56 09'],[50.8443950015395,4.32564512984937,'Delacroix',450,0,'info@abattoir.be',''],[51.096935,3.835332,'P1',25,0,'mobiliteit@lochristi.be','+32 9 326 97 70'],[51.231414515763,5.31476765871048,'P6: Mudakkers 2',180,0,'juridische.dienst@lommel.be',''],[50.982787,4.461927,'P4',22,0,'verkeer@zemst.be','+32 1 562 71 35'],[50.8040328763097,4.94887948036194,'Molenstraat',60,0,'parkeershop.tienen@apcoa.be',''],[51.1616542438733,4.98772859573364,'Centrumparking Werft - Havermarkt',300,0,'info@parkeerbeheer.be',''],[50.237688,4.236275,'P2',40,0,'delphine.lonnoy@beaumont.be','+32 71 79 70 40'],[50.519002,5.246771,'Place Saint Denis',61,0,'info@huy.be','+32 85 24 17 00'],[51.156796,3.233285,'P5',32,0,'mobiliteit@oostkamp.be','+32 5 081 98 80'],[50.22802,5.344997,'Place de la Brigade',66,0,'adl@marche.be','+32 84 32 70 78'],[51.3178243158098,3.1417840719223,'Parksuite',250,0,'info@parksuite.be',''],[50.4011106910493,4.52895283699036,'Place St Roch',10,0,'urbanisme@chatelet.be',''],[50.983162,4.465005,'P5',50,0,'verkeer@zemst.be','+32 1 562 71 35'],[51.0344012972022,2.85931259393692,'Parking Noord',88,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.977793,4.471278,'P2',100,0,'verkeer@zemst.be','+32 1 562 71 35'],[50.7157507728937,4.39678430557251,'Parking rue Francois Libert',120,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.8887188993237,3.42798382043838,'Olmstraat',152,0,'verkeer@waregem.be',''],[51.0310564167317,2.86298722028732,'St-Jansplein',34,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.005811,5.719333,'P14',13,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.6731315243183,4.5673406124115,'Parking Gare Villas',335,0,'info@b-parking.be',''],[51.177292,4.841586,'Nonnenvest',30,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.516472,5.239405,'Saint-Remy',32,0,'info@huy.be','+32 85 24 17 00'],[51.003851,3.321215,'Visserspaviljoen',80,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.8444007141925,4.34663071729962,'Panorama',260,0,'mobiliteit@gob.brussels',''],[50.567175,3.444732,'P3',80,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[50.8136871021494,5.18256425857544,'Clockhempoort',244,0,'info@parkeerbeheer.be','011/69.57.66'],[51.003597,4.987851,'Zaal De Hemmekes ',70,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.673733,5.080025,'P3',138,0,'info.be@parkindigo.be','+32 19 80 00 00'],[51.183792,3.007275,'Marktplein',25,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[50.053708,4.495156,'Saint-Joseph',89,0,'info@couvin.be','+32 60 34 01 10'],[50.4490972367594,3.9387971162796,'Place des Alli&#233;s',40,0,'sebastien.gremeaux@ville.mons.be',''],[50.801774,5.344736,'P9: Speelhof',55,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[51.167837,4.457153,'Vismarkt',50,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[51.1355914503199,4.57335412502289,'Wallehof',120,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[51.023459,5.313207,'Heldenplein',97,0,'patrimonium@heusden-zolder.be','+32 1 180 80 87'],[50.25273,4.915423,'P7',25,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.346949,4.287777,'P3',40,0,'secretariat@thuin.be','+32 71 55 94 11'],[50.6678325438555,5.62544658780098,'Noz&#233;',40,0,'parkingshop.herstal@besixpark.com ',''],[51.170491,5.170078,'Sint-Andriesplein',50,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[49.699263,5.313251,'P4',6,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[51.397619,4.762512,'Jan Van Cuyck',72,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.8696696945895,5.51973670721054,'Begijnhof',20,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.500957,4.226484,'P7',60,0,'mobilite@manage-commune.be','+32 6 455 62 81'],[51.3260294629856,4.94048953056335,'P9: Hannuit',123,0,'mobiliteit@turnhout.be','+32 14 44 33 93'],[51.033237447875,2.86454558372498,'Grote Markt',160,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.919517,3.21927,'Brandweer',100,0,'inzegem@parkeren.be','+32 471 51 28 29'],[50.9349031981608,4.03316259384155,'Keizershallen',750,0,'aalst@parkeren.be','+32 5 379 00 07'],[50.80488,5.345936,'P8: Walstraat-Kan. Darisstraat',24,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[51.183517,3.571007,'P3',80,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.628577,6.024979,'Hufengasse',15,0,'info@eupen-info.be','+32 87 55 34 50'],[50.732606,5.695699,'P4',50,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.709129,5.617916,'P2',21,0,'p.deltour@oupeye.be','+32 4 267 07 43'],[51.24914,5.544772,'Posthoorn',63,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.91009,4.198463,'Hopmarkt',170,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.624728,5.569714,'Parking Gare Rue Bovy',110,0,'info@b-parking.be',''],[50.8047922697349,4.94232147932053,'Kapucijnenplein',32,0,'parkeershop.tienen@apcoa.be',''],[51.2308216357252,2.91297286748886,'Kursaal 1',348,0,'info@castelein.com','058/31.25.10'],[50.888625,5.655307,'P4',50,0,'mobiliteit@lanaken.be','+32 8 973 07 66'],[50.841191,3.603163,'De Ham',260,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.844785,3.609407,'Bekstraat',75,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[51.074574,5.21898,'Mijnstadion',300,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.2143237433541,4.25119400024414,'Schoolparking',50,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[51.1675932895295,4.98750597238541,'Centrumparking Stationsstraat',170,0,'info@parkeerbeheer.be',''],[51.069672,3.100387,'Stadkantoor',35,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[51.0734795338094,4.71721708774567,'P10: Cultuurcentrum',100,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.808729,3.185813,'Lauwestraat',50,0,'mobiliteit@wevelgem.be','+32 5 643 34 70'],[50.8871977537771,3.43844711780548,'Damweg',51,0,'verkeer@waregem.be',''],[50.8859435703274,4.4700676202774,'Station achterkant',25,0,'parkeren.zaventem.be@parkindigo.com','+32 2 503 68 80'],[49.842697,5.43532,'P4',30,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[50.4297315753946,4.61001992225647,'Rue des Prairies',51,0,'votremail@sambreville.be',''],[50.8726365240816,4.27506431937218,'Sportveld Gossetlaan',60,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.614985,5.511323,'P7',80,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.8030666659398,4.94438409805298,'Stedelijk zwembad',45,0,'parkeershop.tienen@apcoa.be',''],[50.984847,4.824722,'Station',400,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[50.9478493695765,3.13418805599213,'Onze-Lieve-Vrouwemarkt',73,0,'dipod@roeselare.be','+32 51 22 72 11'],[51.0336591357905,2.86940842866898,'Lange Veldstraat',50,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.95862,4.458175,'P1',100,0,'verkeer@zemst.be','+32 1 562 71 35'],[50.613646,3.392496,'Parking Gare P3',200,0,'info@b-parking.be',''],[50.41077377416,4.43695843219757,'Gare de l&#39;Ouest',250,0,'info@charleroi.be',''],[51.2069186839083,4.38578724861145,'Gerechtshof',350,0,'info@apcoa.be','+32 3 233 94 23'],[50.83213,5.105339,'Gemeentehuis/Passant',100,0,'ludo.devos@zoutleeuw.be','+32 11 78 49 29'],[50.599689,3.615366,'P1',297,0,'info@b-parking.be','+32 2 525 94 35'],[49.691058,5.380182,'P4',10,0,'commune@chiny.be','+32 61 32 53 53'],[51.400561,4.762347,'IKO ',34,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[51.067192,5.757288,'P3',12,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[51.067048,3.101715,'Burg',56,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.8193655391714,4.51398342847824,'Nettenberg',35,0,'info@tervuren.be','+32 2 766 52 01'],[50.854064,2.719952,'P7: CC Ghybe',51,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.606227,4.138444,'P1 : Parking Gare Rue E. Heuchon',120,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.691431,4.206155,'P2',80,0,'commune.de.tubize@tubize.be','+32 2 391 39 11'],[51.123679,4.218862,'P8',40,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.183862,3.569604,'P2',30,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.7361410912583,4.23619106411934,'Beestenmarkt',17,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.1521797908995,4.15673732757568,'Parking Waasland shopping center',2750,0,'info@waaslandshoppingcenter.be',''],[50.9641199250165,5.49708008766174,'Shopping 3',80,0,'temp@temp.be',''],[51.124864,4.218874,'P10',15,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.874776,3.808721,'P2',30,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.442804,3.816673,'Gare',580,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.6454806997702,5.56811571121216,'Cadran Saint-Hubert',40,0,'info@illico-park.be',''],[51.2906466702345,4.49260085821152,'Sint-Antoniuskerk',150,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[51.0145815093188,4.48353230953217,'Stationsparking',750,0,'ruimtelijkeplanningenmobiliteit@mechelen.be',''],[50.869359928366,5.50973474979401,'Station',40,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.714782738034,4.6040815114975,'Parking du moulin &#224; vent',6,0,'parkings@wavre.be',''],[50.005863,5.721303,'P2',15,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.6265806326955,3.77293467521668,'Square de locomotives',200,0,'mobilite@ath.be',''],[50.981421,5.048963,'Citadel',175,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[50.226461,5.341618,'Jardin Perin',41,0,'adl@marche.be','+32 84 32 70 78'],[51.1806427109345,5.11172980070114,'Den Uyt',230,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.502364,4.235375,'P3',50,0,'mobilite@manage-commune.be','+32 6 455 62 81 '],[50.7757705998329,3.88244926929474,'Boelarestraat',38,0,'mobiliteit@geraardsbergen.be',''],[50.8273210097326,4.51296150684357,'Paleizenlaan',50,0,'info@tervuren.be','+32 2 766 52 01'],[50.569713,3.450966,'P1',20,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[50.461286,4.378571,'P1',200,0,'cathy.vanthuyne@courcelles.be','+32 7 146 69 70'],[50.4669804137546,4.884412586689,'',276,0,'equipement.urbain@ville.namur.be',''],[50.513342,3.591493,'Gare 2',70,0,'environnement@peruwelz.be','+32 69 25 40 42'],[51.024899,5.315363,'Jogem',40,0,'patrimonium@heusden-zolder.be','+32 1 180 80 87'],[50.248917,4.432512,'P6',30,0,'travaux@walcourt.be','+32 71 61 06 27'],[51.184649,3.002251,'Stadhuis',46,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[51.0730615789622,4.71070200204849,'P4: Spoor',244,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.9265420447404,4.42504808306694,'Portaelsplein',58,0,'vilvoorde@parkeren.be',''],[50.604272,3.388901,'Reine Astrid',120,0,'info@q-park.be',''],[50.412552901145,4.44033265113831,'Expo',123,0,'info@charleroi.be',''],[51.0358282377921,3.75764608383179,'P&amp;R Gentbrugge - Land van Rodelaan',250,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[51.129491,2.670869,'P10',50,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[51.3041968428367,4.50435966253281,'Rerum Novarumlei',44,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[50.9631435144751,5.49694061279297,'P3 Katteberg',37,0,'info@parkeren.be',''],[51.0270196358837,4.47942048311234,'Center parking',70,0,'parkeren.mechelen.be@parkindigo.com','+32 15 27 45 43'],[51.3143515130779,4.42578032612801,'Watertoren',196,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.6835214536031,4.37112092971802,'Rue des Foss&#233;s',50,0,'info@zpbrainelalleud.be','+32 2 386 05 11'],[50.8188218763363,5.18574267625809,'Gasthuisstraat',118,0,'parkeren.sint-truiden.be@parkindigo.com',''],[50.5775445566389,4.07013416290283,'P11',90,0,'aleduc@rauwers.be',''],[51.072361,2.665256,'Nieuwpoortstraat',10,0,'info@parkeren.be','+32 16 23 56 09'],[50.9664443076721,5.50410747528076,'P7 Weg naar As',39,0,'info@parkeren.be',''],[50.8496339837783,4.35635548294841,'Radisson SAS Hotel',65,0,'info@carlsonrezidor.com',''],[50.796488,3.12312,'Parking 76',50,0,'info.be@parkindigo.be','+32 5 659 11 20'],[51.2849104196415,4.49001789093018,'Ruiterhal',150,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[49.680474,5.809544,'Parking Gare 1',105,0,'info@b-parking.be',''],[51.2088998682193,4.26115304231644,'Stationsplein',113,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.557675,4.69053,'P7',30,0,'info@q-park.be','+32 2 711 17 62'],[51.088084,5.223115,'Kardijk',30,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.998774,3.324955,'Minderbroedersplein',102,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.9415896866712,4.03569594025612,'Esplanadeplein',68,0,'aalst@parkeren.be','+32 5 379 00 07'],[50.7675708683445,3.87940764427185,'Zwembad Den Bleek',92,0,'mobiliteit@geraardsbergen.be',''],[50.8866368293969,3.43282118439674,'Markt',85,0,'verkeer@waregem.be',''],[50.723305,4.865731,'Pr&#233; Pastur',102,0,'environnement@jodoigne.be','+32 10 81 99 93'],[50.8926492823399,3.42475712299347,'Station oost',182,0,'verkeer@waregem.be',''],[50.8449394739679,4.25756961107254,'Roelandsveld',30,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.52085,5.233379,'Avenue des Foss&#233;s',86,0,'info@huy.be','+32 85 24 17 00'],[51.400367,4.762848,'PAX ',30,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.518741,5.237203,'Quai de Namur',52,0,'info@huy.be','+32 85 24 17 00'],[51.131477,5.448845,'P4: Bomerstraat',60,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.841419,3.602354,'Smallendam',20,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.4059465276796,4.43581581115723,'Charleroi Sud P1',325,0,'info@b-parking.be',''],[51.3132794239536,3.12879413366318,'Basisschool Sint Pieterscollege',30,0,'sintpieter@vbsblankenberge.be',''],[50.9286512060553,4.42375659942627,'Grote markt',100,0,'vilvoorde@parkeren.be',''],[50.781086,3.044485,'Gasstraat',44,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[50.8080602327265,4.93708312511444,'Grote Markt',110,0,'parkeershop.tienen@apcoa.be',''],[50.855934,2.724885,'P6: Gasthuis &amp; oude kliniek',105,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.308246,4.283636,'P6',20,0,'secretariat@thuin.be','+32 71 55 94 11'],[50.9644814282122,5.50126433372498,'Shopping 2',140,0,'temp@temp.be',''],[50.8096059945018,4.93253409862518,'Leuvensestraat',54,0,'parkeershop.tienen@apcoa.be',''],[50.6691333467518,4.62460041046143,'R&#233;dim&#233;',60,0,'walter.leonardva@skynet.be',''],[50.672453,5.077484,'P1',50,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.7730245114131,3.87867540121078,'Middenschool',36,0,'mobiliteit@geraardsbergen.be',''],[51.351689,4.642823,'P2',70,0,'kristine.vanbavel@brecht.be','+32 3 660 25 58'],[50.8016495212543,4.94317710399628,'Vinkenboschvest',142,0,'parkeershop.tienen@apcoa.be',''],[50.983519,4.975079,'Kerkhof',175,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[51.156322,3.231364,'P1',60,0,'mobiliteit@oostkamp.be','+32 5 081 98 80'],[50.930488,5.367944,'Kinepolis',400,0,'info@kinepolis.be','+32 11 29 86 00'],[51.146675,2.716333,'Elisalaan',160,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.341351,4.286106,'P4',50,0,'secretariat@thuin.be','+32 71 55 94 11'],[51.177718,4.834866,'het Hof',20,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[51.068927,3.099317,'Karel de Goede',35,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.600503,5.510825,'P14',120,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.6258081370931,3.7809544801712,'Pont Carr&#233;',150,0,'mobilite@ath.be',''],[50.6253878533179,3.77759367227554,'Rue de la Sucrerie',400,0,'mobilite@ath.be',''],[51.06823,5.754546,'P4',50,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[51.160501,4.670655,'P4',64,0,'vergunningen@nijlen.be','+32 3 410 02 11'],[50.9339058907255,5.35057246661358,'Boudewijnlaan',330,0,'parkeren@hasselt.be','+32 11 23 97 58'],[51.072695,3.103587,'De Mast',135,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.599149,3.613688,'P5',80,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[50.4471962931649,4.63498190045357,'Saint Victor',50,0,'votremail@sambreville.be',''],[50.582058064384,4.08012807369232,'P1',290,0,'aleduc@rauwers.be',''],[50.348882,5.450309,'P2',120,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[50.55945,4.698134,'P4',40,0,'info@q-park.be','+32 2 711 17 62'],[50.735179,4.2334,'Slachthuisstraat',32,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.399089,4.749686,'Zwembad ',200,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.266884,4.907116,'P4',100,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.6862490024408,4.37100291252136,'Esplanade Baron Snoy',80,0,'info@info.be',''],[51.0729891106006,4.72422033548355,'P9: KTA',58,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.5937400791002,5.86886376142502,'S&#232;cheval',52,0,'parkingshop.verviers@besixpark.com',''],[50.513342,3.594936,'Gare 1',108,0,'environnement@peruwelz.be','+32 69 25 40 42'],[50.423658,6.028481,'Warchenne',19,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.184362,3.578292,'P1',400,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.730775,4.234544,'Suikerkaai',58,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.610962,4.450761,'Parking de la place de l&#39;Eglise',30,0,'info@genappe.be','+32 67 79 42 72'],[50.424597,6.029599,'St. Gereon',95,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.424108,6.035904,'Werson',33,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.2130753677295,4.25530314445496,'Grote markt',50,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[51.10062,4.132977,'parking Kaaiplein',90,0,'gemeente@hamme.be','+32 5 247 55 11'],[50.986981,4.839179,'Bekaflaan 2',22,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[51.126152,4.207194,'P4',20,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.9411688516186,4.04658168554306,'Hoveniersplein',69,0,'aalst@parkeren.be','+32 5 379 00 07'],[50.7359866139263,4.24057245254517,'Leide',198,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.177139,4.836271,'Grote Markt',100,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.4490505000235,3.96118819713592,'Pont Rouge',200,0,'sebastien.gremeaux@ville.mons.be',''],[50.7769137663811,3.88578593730926,'Focus/Arjaan',160,0,'mobiliteit@geraardsbergen.be',''],[50.7380779560986,4.23664703965187,'Meiboom',23,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.054203,4.493088,'Rue de la Gare',15,0,'info@couvin.be','+32 60 34 01 10'],[50.926698,5.344757,'Jessa',440,0,'danny.bertels@jessazh.be','+32 11 33 68 02'],[50.990569,4.842015,'August Reyerslaan',100,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[50.7687515554167,3.87700170278549,'Kattestraat',30,0,'mobiliteit@geraardsbergen.be',''],[51.400745,4.762914,'St.-Catharinakerk ',14,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.8713657551238,4.26528364419937,'Gemeenteplein Groot-Bijgaarden',42,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.638384,5.790711,'P1',50,0,'urbanisme@herve.be','+32 87 69 36 00'],[51.180073,3.575699,'P10',100,0,'eeklo@parkeren.be','+32 16 23 56 09'],[51.100364,4.137026,'parking Meulenbroekstraat',50,0,'gemeente@hamme.be','+32 5 247 55 11'],[50.7737589659259,3.89165997505188,'Driepikkel',50,0,'mobiliteit@geraardsbergen.be',''],[50.183406,5.575155,'P1',100,0,'college.echevinal@la-roche-en-ardenne.be','+32 84 41 12 39'],[51.254829,5.47971,'Michielsplein',30,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.695034,4.04316,'P3',100,0,'environnement@enghien-edingen.be','+32 2 397 14 40'],[51.075593,5.21966,'Theodardusplein',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.085729,4.914952,'De Busserstraat',48,0,'info@westerlo.be','+32 1 454 75 75'],[50.6605604829979,5.51783367991447,'Rue des Ponts',15,0,'urbanisme@ans-commune.be','+32 4 247 72 43'],[50.842744,3.609055,'Marlboroughlaan',70,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.811993,3.184032,'Nieuwe markt',100,0,'mobiliteit@wevelgem.be','+32 5 643 34 70'],[51.2105600316758,4.25056636333466,'Windekind',30,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[51.2280116499534,5.31191378831863,'P7: Frans Van Hamstraat',50,0,'juridische.dienst@lommel.be',''],[50.7349137463113,4.24054563045502,'Klaar',79,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.073513,2.670304,'Statieplaats',60,0,'info@parkeren.be','+32 16 23 56 09'],[51.098414,4.132163,'parking Hoogstraat',25,0,'gemeente@hamme.be','+32 5 247 55 11'],[51.2321736597861,5.31565546989441,'P4: Molenstraat',52,0,'juridische.dienst@lommel.be',''],[51.039645,5.174212,'Kerk Paal',60,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.271825,4.643862,'P4',120,0,'mobiliteit@zoersel.be','+32 3 298 09 13'],[51.140239,2.696995,'De Wittelaan',60,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.4343623223097,4.60782185196876,'Galerie du petit Bruxelles',30,0,'votremail@sambreville.be',''],[51.123681,2.642605,'P9',40,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.7184577677558,4.60688710212708,'Parking des M&#233;sanges',201,0,'parkings@wavre.be',''],[49.56817,5.530656,'P1',150,0,'jean-pol.stevenin@virton.be','+32 63 44 01 64'],[50.600072,3.625366,'P2',30,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[50.8723220893923,4.68881249427795,'Parking Bodart',1000,0,'infohuis@leuven.be',''],[51.134205,5.455055,'P2: Noordervest',50,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.1037610981655,3.995600938797,'Knokkestraat',52,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[51.16,4.668058,'P2',40,0,'vergunningen@nijlen.be','+32 3 410 02 11'],[50.734683,4.237226,'Vuurkruisenstraat',55,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.251333,4.430988,'P1',15,0,'travaux@walcourt.be','+32 71 61 06 27'],[51.0561174961222,3.73933464288712,'Station Gent-Dampoort',500,0,'info@b-parking.be','+32 2 528 28 28'],[51.123185,4.21722,'P7',100,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.1351875210711,4.56014692783356,'Tramweglei',350,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[51.3110931555097,3.1269434094429,'Sint-Sebastiaan',100,0,'info@bistrodeperse.be',''],[50.780116,3.04307,'Steenakker',101,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[51.180646,4.832275,'Belgi&#235;laan',150,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.8248947550158,3.26026797294617,'P Station',213,0,'info@parko.be','+32 5 628 12 12'],[49.79727,5.068881,'P2',60,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.8347119594603,4.36815583737176,'Tulp',500,0,'informatie@elsene.be',''],[50.6673806369058,4.61880147457123,'Pont Neuf Galil&#233;e',22,0,'walter.leonardva@skynet.be',''],[50.7379319725828,4.24201279878616,'Graankaai',164,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.714097,3.825137,'P1',50,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[50.482964,4.549782,'Place Ferrer',30,0,'urbanisme@fleurus.be','+32 71 82 03 79&#160;'],[50.854059,2.725317,'P3 Burgemeester Bertenplein',29,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[51.170603,5.164236,'Vrijetijdscentrum De Kruierie',11,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[51.1045324512744,3.99152666330338,'Kerkplein',30,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.5941827806675,4.32191848754883,'Saint-Jacques',50,0,'valerie.heyvaert@nivelles.be',''],[51.248166,3.286059,'Damme-Oost',100,0,'info@parkeren.be','+32 16 23 56 09'],[50.9879064101649,3.52725505828857,'Neerleie 2',40,0,'shop.deinze@q-park.be',''],[50.741021294089,4.2440003156662,'Nederhem',100,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.7765406290366,3.86977851390839,'De Veldmuis',52,0,'mobiliteit@geraardsbergen.be',''],[50.5976288997444,4.32726144790649,'Parking les conceptionnistes',160,0,'info@conceptionnistes.be',''],[50.4488436209996,4.63151916861534,'Gare de Auvelais sud',20,0,'votremail@sambreville.be',''],[50.518351,5.241654,'Place Verte',44,0,'info@huy.be','+32 85 24 17 00'],[51.1705112320062,4.14302587509155,'P3: Stationsstraat',200,0,'psg@parkeren.be','+32 471 77 77 03'],[50.5797468618134,4.06800717115402,'P7',140,0,'aleduc@rauwers.be',''],[50.392891,5.932238,'P7',40,0,'urbanisme@stavelot.be','+32 80 86 20 24'],[50.7008578074267,4.40082371234894,'Parking chauss&#233;e bara',50,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.930542,5.365552,'Provinciehuis',200,0,'mobidesk@limburg.be','+32 11 23 83 83'],[50.5794215471137,4.0739107131958,'P4',50,0,'aleduc@rauwers.be',''],[50.7135225644565,4.4001317024231,'Parking rue du gaz',150,0,'mobilit&#233;@waterloo.be','+32 2 352 98 11'],[50.8153411372538,5.186448097229,'Grote Markt',147,0,'parkeren.sint-truiden.be@parkindigo.com',''],[50.9887531394366,3.52751791477203,'Neerleie',50,0,'shop.deinze@q-park.be',''],[50.506816,3.589714,'Rue de Sondeville',42,0,'environnement@peruwelz.be','+32 69 25 40 42'],[51.047059,5.220875,'Kerkhof',20,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.8319731349832,3.26775133609772,'P Broeltorens',320,0,'info@parko.be','+32 5 628 12 12'],[51.0324075548508,4.10397291183472,'Gedempte Dender',270,0,'mobiliteit@dendermonde.be',''],[50.4147718816627,4.44372296333313,'Palais Des Beaux-Arts',2350,0,'info@charleroi.be',''],[50.9654814597809,5.50552368164062,'Shopping 1',1250,0,'info@shopping1genk.be',''],[50.7403694981632,4.24020230770111,'Scheepswerfkaai',70,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.122027,2.638755,'P8',50,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[51.0263482107355,4.4928503036499,'Douaneplein',150,0,'ruimtelijkeplanningenmobiliteit@mechelen.be',''],[50.431863,6.028651,'Steinbach',150,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.7669431921089,3.87171506881714,'Maretak/De Kriebel',109,0,'mobiliteit@geraardsbergen.be',''],[50.911553,4.202238,'Huinegem2',35,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.4135753668217,4.17020201683044,'Parking des Pastures',170,0,'affaires.economiques@binche.be',''],[50.6710610820177,4.60472524166107,'Marathonien piscines',100,0,'walter.leonardva@skynet.be',''],[51.094661,5.792133,'Markt',50,0,'parkeren@maaseik.be','+32 89 56 05 60'],[51.252239,5.546835,'Kerkplein',66,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.567726,3.449352,'P4',50,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[50.854828,2.726507,'P2: Vroonhof',30,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.624049,5.397272,'P1',20,0,'info@grace-hollogne.be','+32 4 224 53 13'],[50.889186,5.655802,'P3',40,0,'mobiliteit@lanaken.be','+32 8 973 07 66'],[50.8320510623459,3.2473611831665,'P P&amp;B Wembley',160,0,'info@parko.be','+32 5 628 12 12'],[50.629231,6.033352,'Bergstra&#223;e',186,0,'info@eupen-info.be','+32 87 55 34 50'],[49.840436,5.435394,'P1',19,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[51.058586,4.361914,'Post',100,0,'info@parkeren.be','+32 1 623 56 09'],[50.80875,3.182546,'Gemeentehuis',50,0,'mobiliteit@wevelgem.be','+32 5 643 34 70'],[51.099639,5.789384,'P4: Sportlaan',300,0,'parkeren@maaseik.be','+32 89 56 05 60'],[50.697582,4.047614,'P2',250,0,'environnement@enghien-edingen.be','+32 2 397 14 40'],[51.1853,3.570047,'P4',100,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.940676,5.164915,'Markt',48,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.424071,6.028941,'Fraternite',150,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.46826769078,4.86029148101807,'',105,0,'info@b-parking.be',''],[50.501044,4.235563,'P4',50,0,'mobilite@manage-commune.be','+32 6 455 62 81 '],[50.195358,4.541698,'P5',80,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[51.0737340125327,4.72917705774307,'P2: Academie',40,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.986698,4.841325,'Bekaflaan 1',50,0,'parkeren.aarschot.be@parkindigo.com','+32 16 66 00 79'],[50.6823828037528,4.36933189630508,'Grand Place',50,0,'info@zpbrainelalleud.be','+32 2 386 05 11'],[50.5957185822904,4.32726144790649,'Recollets',50,0,'valerie.heyvaert@nivelles.be',''],[49.841487,5.43361,'P7',40,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[51.129628,2.751784,'Marktplein',30,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[51.1628064549686,4.99013453722,'Centrumparking Lebonstraat',30,0,'info@parkeerbeheer.be',''],[50.8372914845894,4.35753686525549,'Hilton',130,0,'meet@thehotel.be',''],[50.8190755702147,4.51130658388138,'Lindeboom',40,0,'info@tervuren.be','+32 2 766 52 01'],[51.1629561561954,4.99345779418945,'Centrumparking Nieuwstraat',170,0,'info@parkeerbeheer.be',''],[51.169062,4.450803,'Oude-God',80,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[50.771186,2.996776,'P4',30,0,'info@polcom.be','+32 56 55 00 55'],[49.999497,5.71397,'P5',32,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.15329,3.237194,'P3',50,0,'mobiliteit@oostkamp.be','+32 5 081 98 80'],[50.6834364806877,4.36664700508118,'H&#244;pital',300,0,'info@zpbrainelalleud.be','+32 2 386 05 11'],[50.738234,5.696488,'P8',30,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.9213275556904,4.5050060749054,'Orchidee&#235;nlaan',35,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[50.9501434019443,3.12624244513199,'Moermanparking',307,0,'roeselare@parkeren.be','+32 51 20 61 85'],[51.257231,5.482359,'De Koekoek',32,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.844179,3.595353,'Meerspoort',100,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.7159783430985,4.60602879524231,'Place Henri Berger',26,0,'parkings@wavre.be',''],[50.446735,3.818761,'Avenue de l&#39;enseignement',220,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.4109043102043,3.89223128557205,'Archim&#232;de',29,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[50.990864,3.74738,'Conversatie',64,0,'mobiliteit@merelbeke.be','+32 9 210 33 11'],[50.9312215006018,4.43501383066177,'Olmstraat',20,0,'vilvoorde@parkeren.be',''],[50.4965277331406,5.09475946426392,'Gare',200,0,'parkingshop.andenne@besixpark.com',''],[50.004244,5.721811,'P15',85,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.9307988812248,4.424067735672,'Rooseveltlaan',158,0,'vilvoorde@parkeren.be',''],[50.8717736806364,5.51242232322693,'Vesalius',74,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.6031516987448,4.33523297309876,'SNCB',300,0,'info@b-parking.be',''],[50.722042,4.515594,'P4',80,0,'mobilite@rixensart.be','+32 2 634 35 64'],[51.3112541113401,3.12655448913574,'Middenschool',300,0,'info@parkingdb.be','+32 50 42 41 42'],[51.212561,3.452015,'Sportterreinen',157,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.840097,3.613093,'Desire Waelkensstraat',34,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.501951,4.224776,'P5',30,0,'mobilite@manage-commune.be','+32 6 455 62 81 '],[50.490406,4.228583,'P1',50,0,'mobilite@manage-commune.be','+32 6 455 62 81 '],[50.997008,3.323608,'CC Gildhof',67,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.8019478710757,4.93790119886398,'Moespikvest',39,0,'parkeershop.tienen@apcoa.be',''],[51.0769679612939,4.72626149654388,'P8: Leopoldlei',64,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.610448,5.512715,'P9',110,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.778434,3.046088,'Academie &amp; zwembad',96,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[50.25273,4.916584,'P6',50,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.988506,5.051038,'Verversgracht',350,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[50.7741736816821,3.87845948338509,'Karmelietenstraat',22,0,'mobiliteit@geraardsbergen.be',''],[50.7385973821276,4.2386519908905,'Bresweg',18,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.1056810361986,3.99046182632446,'Sint-Laurentiusplein',22,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.8713031272394,5.51414430141449,'Korenbloem',75,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[51.35594,4.629863,'P4',500,0,'kristine.vanbavel@brecht.be','+32 3 660 25 58'],[51.1901567066083,5.11393994092941,'Hangarstraat',190,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[51.1736106078794,4.14788872003555,'P4: Noordlaan',148,0,'mobiliteit@sint-niklaas.be',''],[50.8842768077191,4.47493180632591,'Spoorwegstraat',43,0,'parkeren.zaventem.be@parkindigo.com','+32 2 503 68 80'],[50.9252199455521,4.42189380526543,'Rondeweg',80,0,'vilvoorde@parkeren.be',''],[49.6836829956016,5.81456780433655,'Grand Place',58,0,'administration@arlon.be',''],[50.9447399132274,4.03860211372376,'Parking Denderstraat',707,0,'info@b-parking.be','NA'],[51.170732,4.447255,'Heilig-Kruis',25,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[50.80726699301,4.94773149490356,'Leopoldvest',370,0,'parkeershop.tienen@apcoa.be',''],[50.8081280303681,4.9236935377121,'Station achteraan',648,0,'parkeershop.tienen@apcoa.be',''],[51.079273,3.061967,'Kateeldomein Wijnendale',56,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[51.155982,2.962237,'Tempelhofstraat',42,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.4812033911004,4.18280303478241,'P9: Nothomb',100,0,'fabian.bertoni@q-park.com',''],[50.112357,4.952525,'P5',30,0,'contact@beauraing.be','+32 82 71 00 10'],[50.516998,5.24073,'Rue des Brasseurs',16,0,'info@huy.be','+32 85 24 17 00'],[50.7743424504362,3.88367235660553,'Abdij',60,0,'mobiliteit@geraardsbergen.be',''],[51.3219899314687,4.93971168994904,'P10: Merode',48,0,'info.be@parkindigo.be','+32 14 43 70 87'],[50.6664167036036,4.62440729141235,'Cyclotron',100,0,'walter.leonardva@skynet.be',''],[50.948309,5.113982,'Stadhuis',40,0,'ruimtelijke.ordening@halen.be','+32 13 61 81 30&#160;'],[50.855804,2.726431,'P1: Grote Markt',47,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.408931727191,3.89437437057495,'Harmonie',100,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[51.268521,4.710371,'P1',30,0,'mobiliteit@zoersel.be','+32 3 298 09 13'],[50.4661438356272,4.87289518117905,'',480,0,'equipement.urbain@ville.namur.be','081246584'],[50.7137705258162,4.61126446723938,'Parking place Bosch',120,0,'parkings@wavre.be',''],[51.1638072573083,4.13995742797852,'P1: Grote Markt',350,0,'psg@parkeren.be','03/776.39.32'],[51.06909,3.103161,'Zwembad',144,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[49.99951,5.710472,'P3',15,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.410834,4.324909,'P3',40,0,'travaux@villedefontaine.be','+32 71 54 81 31'],[50.8172518398836,4.51115906238556,'Diependal',10,0,'info@tervuren.be','+32 2 766 52 01'],[51.099346,4.136628,'parking Jagerstraat',18,0,'gemeente@hamme.be','+32 5 247 55 11'],[51.141793,2.698283,'Eug&#232;ne Debongnieplein',40,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.815469901117,4.26404875337266,'Lennik',575,0,'Central.Telephonique@erasme.ulb.ac.be',''],[51.2293217662838,5.31627774238586,'P3: Michiel Jansplein',149,0,'juridische.dienst@lommel.be',''],[51.065168,3.101334,'Sint Pieterskerk',33,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[51.002928,3.329311,'Sportlaan',102,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.6382649,5.57477811115,'Kennedy',75,0,'kennedy@uhoda.com','+3242229054'],[51.065427,3.105233,'Station',50,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.563987,4.693841,'Parking de l&#39;ancienne coutellerie Pierard',85,0,'info@q-park.be','+32 2 711 17 62'],[51.2322240450081,5.30687928199768,'P1: Adelberg',184,0,'juridische.dienst@lommel.be',''],[51.139638,5.597725,'Stationswal',50,0,'http://parkeren.bree.be/mailons','+32 89 84 85 23'],[50.450695,3.81671,'Rue du Port',35,0,'info@saint-ghislain.be','+32 65 76 19 00'],[51.040476,5.175994,'De wissel',50,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.158477,5.220378,'Parking de la Biblioth&#232;que',33,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[50.8033565311487,4.94579762220383,'&#39;t Schip',8,0,'parkeershop.tienen@apcoa.be',''],[49.79418,5.066961,'P8',30,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.600290,5.465923,'P3',30,0,'roland.welliquet@flemalle.be','+32 4 234 89 05'],[50.610543,4.444788,'Parking de l&#39;Espace 2000',200,0,'info@genappe.be','+32 67 79 42 72'],[50.5924936815571,5.8523440361023,'Harmonie',130,0,'parkingshop.verviers@besixpark.com',''],[51.159919,4.665242,'P1',60,0,'vergunningen@nijlen.be','+32 3 410 02 11'],[51.084633,4.914428,'Hovenierstraat',44,0,'info@westerlo.be','+32 1 454 75 75'],[50.000155,5.718804,'P8',50,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.518821,5.245231,'Rue du March&#233;',40,0,'info@huy.be','+32 85 24 17 00'],[50.977302,4.974487,'GC den egger',415,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.8454560202881,2.89371997117996,'Leopold III-laan',100,0,'parkeren@ieper.be','+32 5 745 18 44'],[50.943502,5.167336,'Kerkhof',50,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[51.1008726201104,3.98782253265381,'Windekind',70,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.770841,2.999574,'P2',35,0,'info@polcom.be','+32 56 55 00 55'],[50.8668326330356,5.51271468400955,'Eikenlaan',141,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.4528699125866,4.62396204471588,'Rue de la Bach&#233;e',60,0,'votremail@sambreville.be',''],[50.995595,3.745637,'Stelplaats',55,0,'klacht.merelbeke@apcoa.be','+32 3 233 94 23'],[50.394652,5.930317,'P6',100,0,'urbanisme@stavelot.be','+32 80 86 20 24'],[51.074556,2.662357,'Noordstraat',20,0,'info@parkeren.be','+32 16 23 56 09'],[50.718379650898,4.61147367954254,'Parking de la rue de l&#39;H&#244;tel',14,0,'parkings@wavre.be',''],[50.838911,3.604426,'AC Maagdendale',100,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.839182559273,4.0234100818634,'Station noord',90,0,'ninove@parkeren.be',''],[50.710555,3.828678,'P9',100,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[51.180606,3.568805,'P8',30,0,'eeklo@parkeren.be','+32 16 23 56 09'],[50.6676968435195,4.6197697520256,'Parking Sciences',23,0,'walter.leonardva@skynet.be',''],[50.132145,5.788172,'P2',40,0,'vinciane.hazee@houffalize.be','+32 61 28 00 64'],[50.9262242,5.335042477,'Truierbrug noord',70,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.990243,3.330381,'Goederenkoer',220,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.293738,4.329639,'P7',100,0,'secretariat@thuin.be','+32 71 55 94 11'],[51.094972,4.143705,'parking Tweebruggenplein',100,0,'gemeente@hamme.be','+32 5 247 55 11'],[51.3349151799654,4.91960048675537,'AZ Campus Sint-Jozef',698,0,'info@azturnhout.be','+32 14 40 60 11'],[50.609755,4.136463,'P6',22,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.9658091681456,5.50684332847595,'Gratis P4 Europalaan',47,0,'mobiliteit@genk.be',''],[51.065231,3.097721,'De Gheldere',73,0,'bert.desendere@torhout.be','+32 50 22 11 39&#160;'],[50.508195,3.591211,'Administration communale',20,0,'environnement@peruwelz.be','+32 69 25 40 42'],[51.2084841201163,3.23348487293083,'Langestraat',200,0,'info@alfa.be','+32 50 44 86 63'],[50.884837257174,3.42922300100326,'De Treffer',20,0,'verkeer@waregem.be',''],[50.8842669974053,3.43343406915665,'Schakelstraat',137,0,'verkeer@waregem.be',''],[50.4117755296023,4.1627562046051,'Parking des Boulevards',25,0,'affaires.economiques@binche.be',''],[50.396629,4.697573,'P3',15,0,'accueil@fosses-la-ville.be','+32 71 26 60 55&#160;'],[50.8896748775022,3.43209564685822,'M. Windelstraat',92,0,'verkeer@waregem.be',''],[50.735911,5.688989,'P11',50,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.467489,3.811537,'Place de Tertre',100,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.8698609699727,5.52065134048462,'Brandweer',22,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.613652,6.045714,'H&#252;tte',80,0,'info@eupen-info.be','+32 87 55 34 50'],[50.199835,4.553751,'P7',125,0,'h&#233;l&#232;ne.masson@commune-philippeville.be','+32 71 66 04 08'],[51.1289244825738,4.58258360624313,'Zaat',90,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.18006,5.576537,'P3',10,0,'college.echevinal@la-roche-en-ardenne.be','+32 84 41 12 39'],[50.393923,5.932566,'P4',12,0,'etc@abbayedestavelot.be','+32 80 88 08 78'],[51.1874299766555,5.11888593435288,'Wereldwinkel',25,0,'verkeersdienst@gemeentemol.be','+32 1 433 09 80'],[50.516797,5.234843,'Pont de Fer',90,0,'info@huy.be','+32 85 24 17 00'],[50.599878,5.471701,'P2',45,0,'roland.welliquet@flemalle.be','+32 4 234 89 05'],[50.8729077286552,5.51237940788269,'Ganshof',78,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.281184,6.127791,'P6',50,0,'kontakt@st.vith.be','+32 80 28 01 30'],[51.088073,4.91165,'Denis Voetsstraat',125,0,'info@westerlo.be','+32 1 454 75 75'],[50.505458,4.235085,'P2',100,0,'mobilite@manage-commune.be','+32 6 455 62 81 '],[49.687509,5.370171,'P7',12,0,'commune@chiny.be','+32 61 32 53 53'],[50.642644,5.793821,'P2',100,0,'urbanisme@herve.be','+32 87 69 36 00'],[50.737114,5.694124,'P6',30,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.8743751796889,5.51962405443192,'Politie',40,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.8691838854418,5.52297413349152,'Tabaart',36,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.4080227642428,4.44133847951889,'City Parking Albert 1er ',264,0,'info@charleroi.be',''],[50.8321766222956,4.35988017690151,'Louise Concorde',26,0,'mobiliteit@gob.brussels',''],[50.6652708382725,4.6209579706192,'Parking Schwann',100,0,'walter.leonardva@skynet.be',''],[50.15867,5.222869,'Parking du Tunnel',18,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[50.161964,5.21825,'P1',31,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[49.791187,5.065278,'P11',10,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[49.6853524293137,5.80994367599487,'Place Schalbert',75,0,'administration@arlon.be',''],[51.3213529838214,4.94331157252577,'P5: Le Bon',220,0,'lebon@interparking.com','+32 14 43 91 91'],[50.7149321903349,4.61747646331787,'Square Leurquin',74,0,'parkings@wavre.be',''],[50.4665604192386,4.84741687774658,'',475,0,'equipement.urbain@ville.namur.be',''],[51.000438,3.319446,'Keidamstraat',105,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.723257,4.867532,'Parking du Parc',20,0,'environnement@jodoigne.be','+32 10 81 99 93'],[51.097199,3.829158,'P3',50,0,'mobiliteit@lochristi.be','+32 9 326 97 70'],[50.9782367303692,3.53742331266403,'parking station 2',150,0,'shop.deinze@q-park.be',''],[51.160323,4.672342,'P3',210,0,'vergunningen@nijlen.be','+32 3 410 02 11'],[50.748784,3.604364,'P5',60,0,'parkeren.ronse@parkindigo.be','+32 55 60 55 28'],[51.154791,3.235526,'P2',15,0,'mobiliteit@oostkamp.be','+32 5 081 98 80'],[51.133493,2.748829,'Vismijn',60,0,'info@parkeerbedrijfnieuwpoort.be','+32 58 24 04 79'],[50.920453,3.218867,'Goederenstation',180,0,'inzegem@parkeren.be','+32 471 51 28 29'],[51.006284,4.98936,'Station',150,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.513974,3.592001,'Gare 3',150,0,'environnement@peruwelz.be','+32 69 25 40 42'],[51.115093,2.621289,'P3',100,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[50.632074,5.478507,'P5',15,0,'info@grace-hollogne.be','+32 4 224 53 13'],[49.796289,5.071066,'P5',20,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.92803,5.35888,'Japanse tuin',92,0,'parkeren@hasselt.be','+32 11 23 97 58'],[51.3140539592569,4.43280100822449,'Spoor',119,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.610835,5.504024,'P12',30,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.712461,3.82879,'P2',50,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[50.818655809966,5.18586605787277,'Cicindriashopping',85,0,'parkeren.sint-truiden.be@parkindigo.com',''],[49.796228,5.069212,'P4',20,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.77672,3.039818,'Akademiestraat',50,0,'mobiliteit@wervik.be','+32 56 95 21 73'],[50.4063568034722,4.52789068222046,'P+M Est',100,0,'urbanisme@chatelet.be',''],[50.849124181436,2.87765085697174,'Colaertplein',45,0,'parkeren@ieper.be','+32 5 745 18 44'],[49.838997,5.434722,'P5',45,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[50.615476,5.502467,'P2',25,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[50.9634205603819,5.48851847648621,'Gratis P1 Hooiplaats',162,0,'mobiliteit@genk.be',''],[50.7439561795533,3.21489036083221,'Grand place',125,0,'michel.deweerdt@mouscron.be',''],[51.3129277420614,4.43220555782318,'Station',11,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.494921,3.606267,'Route de Bonsecours',50,0,'environnement@peruwelz.be','+32 69 25 40 42'],[50.558126,4.696836,'P6',30,0,'info@q-park.be','+32 2 711 17 62'],[50.497576,3.60727,'Rue du Ch&#226;teau',15,0,'environnement@peruwelz.be','+32 69 25 40 42'],[50.477189,4.106596,'P5',120,0,'frederic.petre@leroeulx.be','+32 64 31 07 45'],[50.632411,6.036537,'Auf&#39;m Hund',32,0,'info@eupen-info.be','+32 87 55 34 50'],[51.1621655936632,4.14010763168335,'P6: H. Heymanplein',170,0,'mobiliteit@sint-niklaas.be',''],[50.9442210869486,3.1260958313942,'Botermarkt',60,0,'dipod@roeselare.be',''],[50.4538891114194,3.95973443984985,'Av. B. de Constantinople',92,0,'sebastien.gremeaux@ville.mons.be',''],[51.251492,5.548083,'Bibliotheek',10,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.752623,2.949181,'P12',30,0,'info@polcom.be','+32 56 55 00 55'],[50.713012,3.836018,'P6',23,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[50.601298,4.136391,'P4',30,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.8405908922836,4.40244474176108,'Linthout',73,0,'elisabeth.koch@shopping-linthout.eu',''],[50.94117,5.161062,'Diestsesteenweg',36,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.796159,3.11947,'Grote Markt',58,0,'info.be@parkindigo.be','+32 5 659 11 20'],[51.153846,2.961327,'Congolaan-stadhuis',22,0,'openbarewerken@gistel.be','+32 59 27 02 27'],[50.9610757538213,5.48508524894714,'Gratis P3 Kerk Termien',120,0,'mobiliteit@genk.be',''],[49.791071,5.066332,'P9',10,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.9254093,5.33363699899996,'Truierbrug zuid',150,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.568704,3.445348,'P2',10,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[51.031894767993,2.86837041378021,'Parking Station',70,0,'parkeren.diksmuide.be@parkindigo.com',''],[50.75234,5.080828,'Stationspoortparking',50,0,'info@parkeren.be','+32 16 23 56 09'],[49.840179,5.435066,'P2',32,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[50.840789,3.606138,'Margaretha van Parmastraat',100,0,'http://www.oudenaarde.be/contact','+32 55 33 51 18'],[50.990602,3.328912,'Station 1',23,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.61701,5.507742,'P5',18,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[51.0715599439118,4.71371948719025,'P11: Berkenstraat',48,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.8252810609784,3.25575113296509,'Appel',308,0,'info@parko.be','+32 5 628 12 12'],[51.3169132412449,4.43295657634735,'Sporthal',42,0,'openbarewerken@kapellen.be','+32 3 660 66 00'],[50.819113338334,5.1782351732254,'Station achterzijde',160,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.559003,4.69642,'P5',30,0,'info@q-park.be','+32 2 711 17 62'],[50.771399,3.002058,'P3',15,0,'info@polcom.be','+32 56 55 00 55'],[51.254216,5.48102,'OCMW',100,0,'openbarewerken@hamont-achel.be','+32 11 51 06 10'],[50.7156556686001,4.61258411407471,'Pont Neuf',16,0,'parkings@wavre.be',''],[51.3248260215111,4.93915915489197,'Renier Sniederstraat',53,0,'info@b-parking.be','+32 2 525 94 35'],[50.8168019269219,5.17633616924286,'Station',243,0,'parkeren.sinttruiden.be@parkindigo.com',''],[51.0862994758096,3.70602965354919,'P&amp;R Wondelgem Botestraat',20,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[50.980865,2.749283,'Sint-Pieterskerk',5,0,'stadsbestuur@lo-reninge.be','+32 58 28 80 20'],[50.734025,4.230055,'Stadparking Gamma',20,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.071111,2.659819,'Oude Beestenmarkt',30,0,'info@parkeren.be','+32 16 23 56 09'],[50.8218146266286,4.51676487922668,'Duisburgsesteenweg',50,0,'info@tervuren.be','+32 2 766 52 01'],[51.175777,4.839083,'Nonnenstraat',100,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.7170100374434,4.61122691631317,'Parking de la Cure',15,0,'parkings@wavre.be',''],[50.424589,6.026989,'Parc',61,0,'accueil@malmedy.be','+32 80 79 96 64'],[50.6359822820719,3.7785941362381,'Belle park',150,0,'mobilite@ath.be',''],[50.9370498753483,3.12308102846146,'De Spil',108,0,'info@despil.be','+32 51 26 57 00'],[50.93480178,5.33815920400002,'Kolenkaai',100,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.634607,6.040567,'Holftert',95,0,'info@eupen-info.be','+32 87 55 34 50'],[51.1673460547955,4.14361596107483,'Central parking',35,0,'mobiliteit@sint-niklaas.be',''],[50.853184,2.724068,'P8: Oudstrijdersplein',135,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[50.253294,4.916374,'P5',70,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.983983,5.061579,'Halve Maan',300,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[50.7371307526262,4.23650622367859,'Dekenstraat',10,0,'openbare.werken@halle.be','+32 2 365 95 10'],[50.230067,5.346151,'Ourthe',34,0,'adl@marche.be','+32 84 32 70 78'],[50.8814815927085,4.69965398311615,'P12 Vismarkt',58,0,'infohuis@leuven.be',''],[50.606985,4.130663,'P8',50,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.8204367562653,5.19288003444672,'Begijnhof',123,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.911555,4.195851,'Nieuwstraat',40,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.000991,5.715782,'P1',100,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.425653,6.019664,'Malmedy-Expo',200,0,'accueil@malmedy.be','+32 80 79 96 64'],[51.006008,3.883253,'P4',100,0,'wetteren@parkeren.be','+32 9 369 79 88'],[50.8464687731955,2.87824362516403,'Goederenstation',52,0,'parkeren@ieper.be','+32 5 745 18 44'],[51.006734,3.884778,'P1',75,0,'wetteren@parkeren.be','+32 9 369 79 88'],[51.0301876866491,4.48733031749725,'Zwartzustersvest (St-Maarten ziekenhuis)',200,0,'azsintmaarten@emmaus.be',''],[50.995969,3.316438,'De Vlinder',26,0,'tielt@parkeren.be','+32 51 42 81 40'],[51.346787208499,3.28509986400604,'Abraham Hansplein',60,0,'knokke-heist@parkeren.be','+32 5 034 23 06'],[51.168586,5.166436,'Stationsplein',40,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.8498788958685,4.26177263259888,'Bibliotheek',49,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.944753433053,3.12433898448944,'Grote Markt',70,0,'dipod@roeselare.be','+32 51 22 72 11'],[50.560992,4.694383,'P2',55,0,'info@q-park.be','+32 2 711 17 62'],[50.9271067150396,4.42430913448334,'OLV Van Goede Hoop',45,0,'vilvoorde@parkeren.be',''],[50.695454,4.041262,'P4',65,0,'environnement@enghien-edingen.be','+32 2 397 14 40'],[50.8842060790351,3.42698872089386,'Kwaestraatje',33,0,'verkeer@waregem.be',''],[50.984875,5.054212,'Graan- en veemarkt',165,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[49.693362,5.412746,'P1',10,0,'commune@chiny.be','+32 61 32 53 53'],[50.048889,4.31486,'P1',15,0,'accueil@ville-de-chimay.be','+32 60 21 02 92'],[51.070116,2.664257,'Lindendreef',100,0,'info@parkeren.be','+32 16 23 56 09'],[50.514441,5.236727,'Place du Tilleul',18,0,'info@huy.be','+32 85 24 17 00'],[50.7404543679381,3.22758257389069,'Gare de Mouscron',100,0,'michel.deweerdt@mouscron.be',''],[51.1668826963333,4.13753941655159,'P7: Schouwburgplein',70,0,'mobiliteit@sint-niklaas.be',''],[50.8355508832321,4.19249653816223,'Marktplein Schepdaal',50,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.397674,4.696283,'P1',50,0,'accueil@fosses-la-ville.be','+32 71 26 60 55&#160;'],[50.7150459775627,4.60771322250366,'Parking Nivelles',68,0,'parkings@wavre.be',''],[50.8053380761355,4.93995845317841,'Veemarkt',43,0,'parkeershop.tienen@apcoa.be',''],[50.625546,6.044625,'Frankendelle',50,0,'info@eupen-info.be','+32 87 55 34 50'],[50.8118330218611,4.9288621544838,'&#39;t Hoekske',63,0,'parkeershop.tienen@apcoa.be',''],[50.646637,5.797715,'P3',200,0,'urbanisme@herve.be','+32 87 69 36 00'],[50.6426878471196,5.57612478733063,'Grand Poste',43,0,'info@illico-park.be',''],[50.288758,5.097841,'Lambert',130,0,'contact@ciney.be','+32 83 23 10 24'],[50.871511,3.812241,'P7',60,0,'parkeerwinkel.zottegem@parkeerbeheer.be','+32 9 360 48 77'],[50.9659071423624,5.50008416175842,'P4 Fruitmarkt',65,0,'info@parkeren.be',''],[51.054434,5.739603,'P5',50,0,'kristel.geerits@dilsen-stokkem.be','+32 89 79 09 53'],[51.3519108930867,3.28779548406601,'Leopoldlaan',61,0,'knokke-heist@parkeren.be','+32 5 034 23 06'],[51.037707,5.168962,'Parochiezaal',40,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.148217,5.597571,'Zwembad',50,0,'http://parkeren.bree.be/mailons','+32 89 84 85 23'],[50.807138,4.279777,'Parking Centrum',50,0,'mobiliteit@sint-pieters-leeuw.be','+32 2 371 22 92'],[50.804441,5.341469,'P2: Gr. Lodewijkplein',40,0,'nathalie.francis@borgloon.be','+32 12 67 36 14&#160;'],[50.410846294229,3.89065146446228,'J.Bidez-Lambrechies',49,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[50.7757434620086,3.87025058269501,'Kerk Nederboelare',67,0,'mobiliteit@geraardsbergen.be',''],[51.404371,4.754033,'Burgemeester Brosensstraat',47,0,'openbare.werken@hoogstraten.be','+32 3 340 19 44'],[50.4809951642597,4.19355869293213,'P2',14,0,'fabian.bertoni@q-park.com',''],[50.9344282113158,4.04309213161468,'Houtmarkt',75,0,'aalst@parkeren.be','+32 5 379 00 07'],[50.752522,5.083917,'Rufferdingeplein',20,0,'info@parkeren.be','+32 16 23 56 09'],[50.5950834952343,4.32410448789597,'Sacre-Coeur',50,0,'valerie.heyvaert@nivelles.be',''],[51.181889,3.01044,'Sporthal',75,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[50.614464,4.451488,'Parking des Lilas',68,0,'info@genappe.be','+32 67 79 42 72'],[49.68689,5.370482,'P6',6,0,'commune@chiny.be','+32 61 32 53 53'],[50.004599,5.722551,'P16',20,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.1289404736757,4.56970363855362,'Schappekoppenstraat',34,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.109679,4.957212,'P2',14,0,'contact@beauraing.be','+32 82 71 00 10'],[50.9151220603501,5.32095551526356,'Carpoolparking Hasselt-Zuid (afrit 28)',220,0,'parkeren@hasselt.be','+32 11 23 97 58'],[51.130552,4.21224,'P3',50,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.912993,4.190531,'Putberg',50,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.9656858554755,5.50420135259628,'P5 Shopping 1 - Europalaan',250,0,'info@parkeren.be',''],[50.9332676454878,4.49755221605301,'Kerkdreef',50,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[50.352583,5.458582,'P5',60,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[50.79697,3.122066,'Donkerstraat',6,0,'info.be@parkindigo.be','+32 5 659 11 20'],[50.565926,3.449295,'P5',25,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[51.0988090343967,3.71185004711151,'P&amp;R Wondelgem industrieweg',46,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[50.006182,5.724407,'P17',150,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.292585,5.099182,'Capelle',70,0,'contact@ciney.be','+32 83 23 10 24'],[51.108210517016,3.99447977542877,'Zwembad',54,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.4094321328615,3.89365017414093,'Grand-place',70,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[50.6661267341764,5.63304662704468,'Browning',100,0,'parkingshop.herstal@besixpark.com ',''],[50.984896723976,3.52006137371063,'Stadionlaan',60,0,'shop.deinze@q-park.be',''],[50.5772669192111,4.06682699918747,'P10',150,0,'aleduc@rauwers.be',''],[50.4503725411188,3.94033670425415,'Boulevard Gendebien',40,0,'sebastien.gremeaux@ville.mons.be',''],[50.8650331423372,4.24538433551788,'Parkeerplein nabij station/politie',18,0,'openbareruimte@dilbeek.be','+32 2 451 68 00'],[50.63037,5.529451,'P2',13,0,'info@saint-nicolas.be','+32 4 252 98 90'],[50.6726351704467,4.60554599761963,'Lauzelle',99,0,'walter.leonardva@skynet.be',''],[50.725261,4.867109,'Grand Place',92,0,'environnement@jodoigne.be','+32 10 81 99 93'],[51.1040086736009,3.99166747927666,'Markt aan kerk',22,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.158275,5.224875,'Parking du Hableau',116,0,'siegrid.jans@rochefort.be','+32 84 22 06 17'],[50.919606,3.210705,'Kruisstraat',35,0,'inzegem@parkeren.be','+32 471 51 28 29'],[50.772847,4.537292,'Justus Liptiusplein',40,0,'info@overijse.be','+32 2 687 60 40'],[51.177424,4.841589,'Augustijnenlaan',57,0,'https://www.herentals.be/contact-ruimtelijke-ordening','+32 14 28 50 50'],[50.502678,4.107428,'P2',20,0,'frederic.petre@leroeulx.be','+32 64 31 07 45'],[50.473119,4.092828,'P3',50,0,'frederic.petre@leroeulx.be','+32 64 31 07 45'],[50.562139,4.692938,'P1',50,0,'info@q-park.be','+32 2 711 17 62'],[50.114585,4.956459,'P3',100,0,'contact@beauraing.be','+32 82 71 00 10'],[51.117112,2.630354,'P7',50,0,'boekhoudingagb@koksijde.be','+32 5 853 30 77'],[51.0736266006643,3.7770089507103,'P&amp;R Oostakker',200,0,'mobiliteit@stad.gent','+ 32 9 266 28 00'],[50.741545,5.69416,'P9',200,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.8422922960254,2.88676500320435,'Rijselsepoort',120,0,'parkeren@ieper.be','+32 5 745 18 44'],[50.9092108306798,4.51188325881958,'St-Rumoldus',100,0,'info@steenokkerzeel.be','+32 2 254 19 00'],[50.8128414201779,5.18949508666992,'Europaplein',64,0,'parkeren.sinttruiden.be@parkindigo.com',''],[50.7347083368921,4.23348873853684,'Oudstrijdersplein',80,0,'openbare.werken@halle.be','+32 2 365 95 10'],[51.20803,3.451631,'Oud Sint-Jozef',100,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[50.260874,4.909807,'P2',70,0,'vincent.leclere@dinant.be','+32 82 21 32 77'],[50.509568,3.592551,'Grand Place',50,0,'environnement@peruwelz.be','+32 69 25 40 42'],[50.6283195521253,3.77076745033264,'Cit&#233; Fourdin',30,0,'mobilite@ath.be',''],[50.675819,5.083144,'P7',40,0,'info.be@parkindigo.be','+32 19 80 00 00'],[50.284305,6.122946,'P7',100,0,'kontakt@st.vith.be','+32 80 28 01 30'],[51.002239,3.329967,'Tramstraat',122,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.617115,5.505609,'P4',60,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[51.157785,5.159608,'Nagelsberg',72,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.8091890506394,4.92478787899017,'Station vooraan',339,0,'parkeershop.tienen@apcoa.be',''],[50.565386,3.448752,'P6',20,0,'frederic.vancauter@antoing.net','+32 69 33 29 50'],[50.8705109618697,5.5188649892807,'C. Huysmansplein',23,0,'parkeerwinkel.bilzen@parkeerbeheer.be',''],[50.600968,3.622783,'P4',12,0,'aufildeleuze@leuze-en-hainaut.be','+32 69 66 98 40'],[51.251984,4.502248,'Het Ven',140,0,'technischedienst@schoten.be','+32 3 680 09 55'],[51.074784,5.222976,'Noordwijkstraat',20,0,'parkeren@beringen.be','+32 11 43 02 68'],[51.0261761353882,4.48262572288513,'Inno',60,0,'contact.inno.mechelen@inno.be',''],[50.851010648767,2.88787007331848,'De Kolve',41,0,'parkeren@ieper.be','+32 5 745 18 44'],[50.932428476762,5.3567790982803,'P+R Boudewijnlaan',500,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.4696539499237,4.18118834495544,'P12',100,0,'fabian.bertoni@q-park.com',''],[50.228643,5.34201,'Accueil',102,0,'adl@marche.be','+32 84 32 70 78'],[50.696645,4.048061,'P1',100,0,'environnement@enghien-edingen.be','+32 2 397 14 40'],[50.5736097875858,4.06968891620636,'P16',140,0,'aleduc@rauwers.be',''],[50.767431,3.003685,'P8',100,0,'info@polcom.be','+32 56 55 00 55'],[51.3236661288121,4.93892312049866,'Stationstraat',29,0,'info@b-parking.be','+32 2 525 94 35'],[51.2912337770489,4.49441939592361,'Chiro',40,0,'mobiliteit@brasschaat.be','+32 3 650 02 95'],[50.8562323832422,4.35750421868823,'Manhattan Center',686,0,'mobiliteit@gob.brussels',''],[50.4108114861069,4.16522920131683,'Parking Grand place',100,0,'affaires.economiques@binche.be',''],[50.69615,5.24798,'P1',350,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[50.4476962946251,4.63634580373764,'Place communale',400,0,'votremail@sambreville.be',''],[50.4751422493587,4.18389201164246,'P8: Nicaise',50,0,'fabian.bertoni@q-park.com',''],[50.447926,3.819396,'Rue d&#39;Ath',15,0,'info@saint-ghislain.be','+32 65 76 19 00'],[50.910406,4.200442,'Gemeenteplein',60,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.770825,4.532298,'Stationsplein',147,0,'info@overijse.be','+32 2 687 60 40'],[50.858462,3.31092,'Vrijdomkaai',100,0,'stad@harelbeke.be','+32 5 673 33 11'],[49.699023,5.317619,'P5',25,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[50.744085,3.604326,'P3',40,0,'parkeren.ronse@parkindigo.be','+32 55 60 55 28'],[50.740001,5.691536,'P10',50,0,'didier.hanozin@publilink.be','+32 4 374 84 92'],[50.9077101055541,4.46410238742828,'P2 Brucargo Melsbroek',570,0,'info@interparking.com','+32 2 715 21 30'],[50.92663672,5.34756302799997,'CCHa achterzijde',350,0,'parkeren@hasselt.be','+32 11 23 97 58'],[50.885664713684,3.43057751655579,'Het Pand',441,0,'verkeer@waregem.be',''],[51.131818,5.454963,'P7: Marktplein',20,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.184213,3.004208,'Mariastraat',50,0,'mobiliteit@oudenburg.be','+32 59 56 84 51'],[50.808166,3.182794,'Guldenbergplein',90,0,'mobiliteit@wevelgem.be','+32 5 643 34 70'],[50.4079949632396,3.89916881918907,'Agrappe',40,0,'parkings.frameries.be@parkindigo.com','+32 6 555 19 98'],[51.264869,4.639969,'P5',70,0,'mobiliteit@zoersel.be','+32 3 298 09 13'],[50.7129824796947,4.60742354393005,'Pont St-Jean',30,0,'parkings@wavre.be',''],[51.170107,5.167458,'Markt',20,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.5818077037764,4.07755047082901,'P3',60,0,'aleduc@rauwers.be',''],[50.4050712607837,4.44308996200562,'Charleroi Sud P2',306,0,'info@b-parking.be',''],[50.8352507771226,4.37946995274928,'Forte dei Marmi',98,0,'mobiliteit@etterbeek.be',''],[50.49788,3.608693,'Rue Royale',15,0,'environnement@peruwelz.be','+32 69 25 40 42'],[49.843161,5.433683,'P8',100,0,'commune@neufchateau.be','+32&#160;61 27 50 90'],[51.1043000361904,3.99374485015869,'Poststraat',52,0,'parkeershop.lokeren@apcoa.be','+32 9 356 35 84'],[50.986063,5.044226,'Kluisberg',160,0,'&#160;parkeren.diest.be@parkindigo.com','+32 13 32 33 10'],[51.2299264227799,5.30490517616272,'P2: Hertog Janplein',111,0,'juridische.dienst@lommel.be',''],[51.00476,3.328219,'Ringlaan',150,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.5790400233464,4.06784892082214,'P9',80,0,'aleduc@rauwers.be',''],[50.613474,4.141361,'P9',100,0,'lena.fanara@7090.be','+32 67 87 48 59'],[50.729775,4.300144,'P4',35,0,'mobiliteit@beersel.be','+32 2 359 17 51'],[50.9641908744637,5.50575971603394,'P6 Shopping 1 - Molenstraat',28,0,'info@parkeren.be',''],[51.0767598426833,4.71790909767151,'P1: Cultuurplein',157,0,'parkeren.heistopdenberg.be@parkindigo.com','+32 1 523 00 07'],[50.632417,6.032215,'Hostert',47,0,'info@eupen-info.be','+32 87 55 34 50'],[50.836101,5.105251,'Oude parking school',32,0,'ludo.devos@zoutleeuw.be','+32 11 78 49 29'],[51.072375,2.664091,'Kaatsspelplaats',13,0,'info@parkeren.be','+32 16 23 56 09'],[50.999127,3.318299,'OCMW',47,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.346774,5.442154,'P1',100,0,'forummobilite@durbuy.be','+32 86 21 96 40'],[51.3203354646211,4.93797361850738,'P7: Loechtenberg',160,0,'mobiliteit@turnhout.be','+32 14 44 33 93'],[50.620777,5.941826,'P1',25,0,'jonathan.jourdan@ville-limbourg.be','+32 87 76 04 22'],[50.025881,5.373218,'P4',40,0,'benedicte.pecquet@saint-hubert.be','+32 61 26 09 84'],[49.791456,5.064834,'P10',20,0,'ecoconseiller@bouillon.be','+32 61 28 03 17'],[50.4465416938952,3.94816875457764,'Place Jean &#39;Avesnes',52,0,'sebastien.gremeaux@ville.mons.be',''],[51.129892,4.20545,'P1',50,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[50.6643425648459,4.6212100982666,'Parking Baudouin 1e',200,0,'walter.leonardva@skynet.be',''],[51.0242757141825,3.69238972352605,'P&amp;R The Loop',169,0,'mobiliteit@stad.gent','+32 9 266 28 00'],[50.580876078124,4.06822443008423,'P6',25,0,'aleduc@rauwers.be',''],[50.4484048963866,3.94637703895569,'Place Nervienne',200,0,'sebastien.gremeaux@ville.mons.be',''],[51.1322084337329,4.57459062337875,'K.T.A',135,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.4781686501744,4.18810844421387,'P4',18,0,'fabian.bertoni@q-park.com',''],[50.706587,5.649549,'P3',48,0,'p.deltour@oupeye.be','+32 4 267 07 43'],[51.212243658588,4.251349568367,'Sint-Maarten',33,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.917248,3.207002,'Sportcentrum',85,0,'inzegem@parkeren.be','+32 471 51 28 29'],[50.714643,3.836328,'P5',110,0,'legrand-sophie@lessines.be','+32 68 25 15 48'],[50.90759,4.199918,'Boekfos',150,0,'mobiliteit@asse.be','+32 2 569 80 76'],[50.8821098417059,4.47330236434936,'Kerkplein',155,0,'parkeren.zaventem.be@parkindigo.com','+32 2 503 68 80'],[51.205203,3.445855,'Stationsplein',29,0,'mobiliteit@maldegem.be','+32 5 072 86 02'],[51.2270542336639,5.31538724899292,'P8: Delhaize',139,0,'juridische.dienst@lommel.be',''],[50.006803,5.718233,'P19',30,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[50.743944,3.602107,'P2',50,0,'parkeren.ronse@parkindigo.be','+32 55 60 55 28'],[50.294945,4.32811,'P8',15,0,'secretariat@thuin.be','+32 71 55 94 11'],[50.998152,3.325148,'Sporthal college',46,0,'tielt@parkeren.be','+32 51 42 81 40'],[50.982014,4.98044,'Mariahal ',248,0,'verkeersdienst@scherpenheuvel-zichem.be','+32 13 35 24 17'],[50.4311233841404,4.61356580257416,'Place St Martin',120,0,'votremail@sambreville.be',''],[51.001054,3.322676,'Keidam',34,0,'tielt@parkeren.be','+32 51 42 81 40'],[49.560888,5.519027,'P5',175,0,'jean-pol.stevenin@virton.be','+32 63 44 01 64'],[50.501771,4.226433,'P6',34,0,'mobilite@manage-commune.be','+32 6 455 62 81'],[50.594942174429,5.86217164993286,'Gymnase',106,0,'parkingshop.verviers@besixpark.com',''],[50.603785,4.135962,'P2 : Parking Gare Rue de l&#39;industrie',235,0,'lena.fanara@7090.be','+32 67 87 48 59'],[49.998779,5.717006,'P7',35,0,'c.leboutte@bastogne.be','+32 61 26 26 38'],[51.071046,5.217536,'Kioskplein',13,0,'parkeren@beringen.be','+32 11 43 02 68'],[50.750847,5.085206,'Sportlaan',85,0,'info@parkeren.be','+32 16 23 56 09'],[50.7715470864566,3.88278186321258,'Markt',56,0,'mobiliteit@geraardsbergen.be',''],[50.5929551249319,5.86727052927017,'Laini&#232;re',30,0,'parkingshop.verviers@besixpark.com',''],[50.696526,5.24879,'P2',75,0,'police.administrative@waremme.be','+32 19 33 67 99 36'],[51.2184063505562,2.88858354091644,'Sleuyter arena',520,0,'info@searena.be',''],[50.519276,5.235304,'Avenue de Batta',124,0,'info@huy.be','+32 85 24 17 00'],[51.060090,4.346336,'Schalk',150,0,'info@parkeren.be','+32 1 623 56 09'],[49.698292,5.310871,'P1',150,0,'rejane.struelens@florenville.be','+32 61 32 51 50'],[50.9634036673861,5.50326526165008,'P1 Windmolen',156,0,'info@parkeren.be',''],[51.0271039850891,4.50224876403809,'Overloopparking Nekker',400,0,'ruimtelijkeplanningenmobiliteit@mechelen.be',''],[51.3227710470527,4.95943129062653,'P&amp;R Boomgaardplein',150,0,'mobiliteit@turnhout.be','+32 14 44 33 93'],[51.141731,3.13597,'P.A. Vynckeplein',82,0,'mobiliteit@zedelgem.be','+32 5 028 82 29'],[51.131145,5.459277,'P6: Pol Kip',30,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.131855,5.457115,'P1: Bogaerdplein',58,0,'info@parkeerbeheer.be','+32 3 235 54 55'],[51.159914,5.163847,'Sportcomplex Bleukens',100,0,'verkeersdienst@balen.be','+32 1 474 40 76'],[50.613581,5.502807,'P1',50,0,'jl.lentz@seraing.be','+32 4 330 86 05'],[51.251456,3.281688,'Markt',20,0,'info@parkeren.be','+32 16 23 56 09'],[49.691091,5.380645,'P5',15,0,'commune@chiny.be','+32 61 32 53 53'],[51.165907,4.46397,'Lepelhof',40,0,'parkeerwinkel.mortsel@besixpark.be','+32 3 235 54 55'],[50.484021,4.549451,'Rue des Tanneries',15,0,'urbanisme@fleurus.be','+32 71 82 03 79&#160;'],[50.854695,2.722931,'P4: Burgemeester De Sagherplein',45,0,'mobiliteit@poperinge.be','+32 57 34 66 06'],[51.1592959060302,4.98971074819565,'Centrumparking Pas',150,0,'info@parkeerbeheer.be',''],[50.684756,4.376469,'Parking Av Albert 1er',100,0,'info@b-parking.be','+32 2 525 94 35'],[50.939993,5.162627,'St. Ursula',165,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.4584329616501,3.95297527313232,'Place du Parc',132,0,'sebastien.gremeaux@ville.mons.be',''],[50.394979,4.697043,'P5',20,0,'accueil@fosses-la-ville.be','+32 71 26 60 55&#160;'],[50.6271285183594,3.78001034259796,'Rue de la station',90,0,'mobilite@ath.be',''],[50.814352204667,4.26750336003032,'Erasme',725,0,'Central.Telephonique@erasme.ulb.ac.be',''],[50.770357,4.53522,'Begijnhofplein',51,0,'info@overijse.be','+32 2 687 60 40'],[51.004357,3.892223,'P5',130,0,'wetteren@parkeren.be','+32 9 369 79 88'],[50.229952,5.343176,'Tourisme',35,0,'adl@marche.be','+32 84 32 70 78'],[50.8392113540824,4.02694791555405,'Station zuid',200,0,'ninove@parkeren.be','054 50 23 55'],[51.2126939595437,4.25983607769012,'Centrum (Gravendreef)',466,0,'mobiliteit@beveren.be','+32 3 750 15 11'],[50.793164,3.12486,'Waalvest',350,0,'info.be@parkindigo.be','+32 5 659 11 20'],[51.1353314212711,4.56894859671593,'Renaat Veremansplein',78,0,'parkeerwinkel.lier@parkeerbeheer.be',''],[50.94309,5.161615,'Sint-Martinus',130,0,'mobiliteit@herk-de-stad.be','+32 13 38 03 30'],[50.110241,4.956932,'P1',65,0,'contact@beauraing.be','+32 82 71 00 10']];
//
//        foreach($parkings as $parking) {
//            dd();
//
//            DB::insert("insert into parkings(naam,stad,adres, latitude, longitude) values(?, ?, ?, ?, ?)" ,
//                [$parking->name_nl,
//                    "brussel",
//                    '',
//                    $parking[0],
//                    $parking[1]]);
//
//        }


//        $json = file_get_contents("C:/parkeerplaatsenpersonenmeteenbeperking.geojson");
//        $data = json_decode($json);
//
//            foreach($data->features as $parking)
//            {
//                $coordinaten = $parking->geometry->coordinates;
//                $lat = $coordinaten[1];
//                $long = $coordinaten[0];
//
//                $eigenschappen = $parking->properties;
//
//                DB::insert("insert into voorbehouden_plaatsen(ID_WESTKANS, ADRES_STRAAT, ADRES_NR, POSTCODE, PARKING_BREEDTE_DATA, PARKING_LENGTE_DATA, GEMEENTE, DEELGEMEENTE, longitude, latitude) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" ,
//                    [$eigenschappen->xKey,
//                        $eigenschappen->gmStraat,
//                        0,
//                        9000,
//                        $eigenschappen->Afmeting2,
//                        $eigenschappen->Afmeting1,
//                        $eigenschappen->voGemeente,
//                        '',
//                        $long,
//                        $lat]);
//            }




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
