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

//        return Stad::all();
    }

    public function parking($parking)
    {
        return response()->json(array(
            'error' => 'false',
            'parking' => Parking::where('naam', $parking)->first()
        ));
    }


    public function parkings($stad)
    {
        return response()->json(array(
            'error' => 'false',
            'parkings' => Parking::where('stad', $stad)->get()
        ));
    }
}
