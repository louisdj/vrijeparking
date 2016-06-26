<?php

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

//    Route::get('/beheer', 'ManagementController@index');

    // Authentication Routes...
    $this->get('login', 'Auth\AuthController@showLoginForm');
    $this->post('login', 'Auth\AuthController@login');
    $this->get('logout', 'Auth\AuthController@logout');

    // Registration Routes... disabled for now
//    $this->get('register', 'Auth\AuthController@showRegistrationForm');
//    $this->post('register', 'Auth\AuthController@register');

    Route::group(['as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth'], 'prefix' => 'beheer'], function() {
        Route::get('/', 'HomeController@index');
    });


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