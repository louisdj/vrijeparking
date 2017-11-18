<?php

namespace App\Http\Controllers;

use App\Stad;

use App\Http\Requests;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

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
