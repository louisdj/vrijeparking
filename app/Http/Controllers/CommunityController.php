<?php

namespace App\Http\Controllers;

use App\Parking;
use App\Parking_Suggestie;
use App\Stad;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Auth;

class CommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $steden = Stad::all();

        return view('community.index', compact('steden'));
    }

    public function toevoegen()
    {
        return view('community.toevoegen');
    }

    public function toevoegenPost(Request $request)
    {
        $parking = new Parking_Suggestie();

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

        $parking->latitude = $request->lat;
        $parking->longitude = $request->long;

        $parking->omschrijving = $request->omschrijving;
        $parking->telefoon = $request->telefoon;
        $parking->totaal_plaatsen = $request->totaal_plaatsen;

        $parking->created_by_id = Auth::user()->id;

        $parking->save();

        return view('community.toevoegen')
            ->with('message', 'Parking succesvol aangemaakt. Wij controleren deze binnenkort.');
    }

    public function lijst()
    {
        $lijst = User::all();

        return view('community.lijst', compact('lijst'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
