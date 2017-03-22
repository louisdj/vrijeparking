<?php

namespace App\Http\Controllers\Admin;

use App\Betaalmogelijkheid;
use App\Blogpost;
use App\Parking;
use App\Stad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

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

    public function newParking()
    {
        return view('beheer.newparking');
    }

    public function newParkingPost(Request $request)
    {
        dd("test");

        $parking = new Parking();

        $parking->naam = $request->naam;
        $parking->stad = $request->stad;
        $parking->adres = $request->adres;

        if($request->afbeelding) {
            $imageName = $parking->naam . '.' .
                $request->afbeelding->getClientOriginalExtension();

            $request->afbeelding->move(
                base_path() . '/public/img/parkings/'.$parking->stad.'/', $imageName
            );
        }

        $parking->latitude = $request->latitude;
        $parking->longitude = $request->longitude;

        $parking->omschrijving = $request->omschrijving;
        $parking->telefoon = $request->telefoon;
        $parking->totaal_plaatsen = $request->totaal_plaatsen;


        $parking->bericht = $request->bericht;
        $parking->bericht_type = $request->type;
        $parking->live_data = $request->live_data;


        $parking->save();

        if($request->betaalmiddelen)
        {
            foreach($request->betaalmiddelen as $betaalmogelijkheid)
            {
                $betaalmiddel = new Betaalmogelijkheid();

                $betaalmiddel->parking_id = $parking->id;
                $betaalmiddel->betaling_id = $betaalmogelijkheid;

                $betaalmiddel->save();
            }
        }



        return view('beheer.newparking');
    }

    public function parkingUpdate($id, Request $request)
    {
        $parking = Parking::where('id', $id)->first();

        $parking->naam = $request->naam;
        $parking->stad = $request->stad;
        $parking->latitude = $request->latitude;
        $parking->longitude = $request->longitude;
        $parking->bericht = $request->bericht;
        $parking->bericht_type = $request->type;

        $parking->save();

        return view('beheer.beheer_parking', compact('parking'));
    }

    public function parkingRemove($id)
    {
        $parking = Parking::where('id', $id)->first();
        $parking->delete();

        return redirect('/beheer');
    }
}
