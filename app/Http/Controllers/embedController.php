<?php

namespace App\Http\Controllers;

use App\Parking;
use App\Stad;
use App\Zone;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class embedController extends Controller
{
    public function index()
    {
        $gebieden_array = [];
        $stad = Stad::where('stad', 'deinze')->first();
        $parkings = Parking::all()->where('stad', strtolower('deinze'))->where('parkandride', 0);
        $parkandrides =  Parking::all()->where('stad', strtolower('deinze'))->where('parkandride', 1);
        $zones = Zone::where('stad', 'deinze')->get();

        foreach ($zones as $zone) {
            array_push($gebieden_array, count(DB::table('zone_gebieden')->distinct()->select('gebied')->where('zone_id', $zone->id)->get()));
        }

        return view('embed.index', compact('stad', 'parkings', 'parkandrides', 'zones', 'gebieden_array'));
    }

    public function test()
    {
        return view('embed.test');
    }
}
