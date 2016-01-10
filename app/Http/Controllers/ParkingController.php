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

        return view('vindParking.index');
    }
}
