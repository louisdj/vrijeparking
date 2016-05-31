<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => 'web'], function () {

    Route::get('/', function () {
            return view('home');
        });

    Route::get('/stad/{stad}', 'ParkingController@stad');
    Route::get('/parking/{parking}', 'ParkingController@parking');

    Route::get('/vindparking', 'ParkingController@vindparking');
    Route::post('/vindparking', 'ParkingController@vindparkingpost');

    Route::get('/team', 'ExtraController@team');
    Route::get('/blog', 'ExtraController@blog');

    Route::get('/blog/{titel}', 'ExtraController@blogPost');

    Route::get('/antwerpen', 'ParkingController@antwerpen');


    Route::get('/taal/{locale}', function ($locale)
    {
        Session::set('locale', $locale);
        App::setLocale($locale);

        return View::make('home');
    });

    Route::get('/beheer', 'ManagementController@index');

});


//Working with DataSources
Route::get('/update', 'ParkingController@enterData');


Route::group(['prefix' => 'api'], function () {

    Route::get('/steden', 'ApiController@steden');
    Route::get('/parking/{parking}', 'ApiController@parking');
    Route::get('/parkings/{stad}', 'ApiController@parkings');
    Route::get('/twitter/{stad}', 'ApiController@twitter');

});

Route::get('/graph', 'ParkingController@graph');