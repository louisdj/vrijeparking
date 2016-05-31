<?php

namespace App\Http\Controllers;

use App\Parking;
use App\Stad;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    public function steden()
    {
        return response()->json(array(
            'error' => 'false',
            'steden' => Stad::all()
        ));
    }

    public function parking($parking)
    {
        $parking = Parking::where('naam', $parking)->first();

        return response()->json(array(
            'error' => !$parking,
            'parking' => $parking
        ));
    }


    public function parkings($stad)
    {
        $parkings = Parking::where('stad', $stad)->get();

        $response = response()->json(array(
            'error' => $parkings->isEmpty(),
            'parkings' => $parkings
        ));

        return $response;
    }


    public function twitter($stad)
    {
        return response()->json(array(
            'error' => 'false',
            'parkings' => Parking::where('stad', $stad)->get()
        ));
    }
}
