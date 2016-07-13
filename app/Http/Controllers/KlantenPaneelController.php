<?php

namespace App\Http\Controllers;

use App\Blogpost;
use App\Parking;
use App\Stad;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class KlantenPaneelController extends Controller
{
    public function index()
    {
        $stad = Stad::where('stad', 'gent')->first();
        $parkings = Parking::all()->where('stad', 'gent');

        return view('klantenpaneel.klantenpaneel', compact('stad', 'parkings'));
    }

    public function parking($id)
    {
        $parking = Parking::where('id', $id)->first();
        $bezetting = DB::table('parkings_historie')
            ->select('bezetting', 'updated_at')
            ->where('parking_id', $id)
            ->get();

        return view('klantenpaneel.parking', compact('parking', 'bezetting'));
    }
}
