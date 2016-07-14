<?php

namespace App\Http\Controllers\Admin;

use App\Blogpost;
use App\Parking;
use App\Stad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;

class ManagementController extends Controller
{
    public function index()
    {
        $steden = Stad::all();
        $parkings = Parking::all();
        $blogs = Blogpost::all();

        return view('beheer.beheerpaneel', compact('steden', 'parkings', 'blogs'));
    }

    public function parking($id)
    {
        $parking = Parking::where('id', $id)->first();

        return view('beheer.beheer_parking', compact('parking'));
    }

    public function parkingUpdate($id, Request $request)
    {
        $parking = Parking::where('id', $id)->first();

        $parking->naam = $request->naam;
        $parking->stad = $request->stad;
        $parking->latitude = $request->latitude;
        $parking->longitude = $request->longitude;
        $parking->bericht = $request->bericht;

        $parking->save();

        return view('beheer.beheer_parking', compact('parking'));
    }
}
