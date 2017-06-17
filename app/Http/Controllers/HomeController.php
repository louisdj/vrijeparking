<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Stad;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $offline_steden = Stad::where('live_data', 0)->orderBy('stad', 'asc')->get();

        return view('home', compact('offline_steden'));
    }
}
