<?php

namespace App\Http\Controllers;

use App\Stad;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function __construct()
    {
        //its just a dummy data object.
        $offline_steden = Stad::where('live_data', 0)->orderBy('stad', 'asc')->get();

        // Sharing is caring
        View::share('offline_steden', $offline_steden);
    }
}
