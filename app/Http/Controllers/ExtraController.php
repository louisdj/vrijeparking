<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ExtraController extends Controller
{
    public function team()
    {
        return view('extra.team');
    }

    public function blog()
    {
        return view('extra.blog');
    }
}
