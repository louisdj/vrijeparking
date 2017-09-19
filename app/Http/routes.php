<?php

Route::group(['middleware' => 'web'], function () {

    Route::get('/', 'HomeController@index');

    Route::get('/test', function() {
        return view('/test/pvnummers_original');
    });

    Route::post('/test', function() {
        return view('/test/pvnummers_original');
    });

    Route::get('/autocomplete', 'ParkingController@complete');

    Route::get('/stad/{stad}', 'ParkingController@stad');
    Route::get('/parking/{parking}', 'ParkingController@parking');

//    Route::get('/vindparking3', 'ParkingController@vindparking');
//    Route::post('/vindparking3', 'ParkingController@vindparkingpost');

    Route::get('/vindparking', 'ParkingController@vindparking');
    Route::post('/vindparking', 'ParkingController@vindparkingpost');
    Route::get('/vindparking/{coords?}', 'ParkingController@vindparkingpost');

    Route::get('/team', 'ExtraController@team');
    Route::get('/blog', 'ExtraController@blog');

    Route::get('/blog/{titel}', 'ExtraController@blogPost');


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
    $this->get('register', 'Auth\AuthController@showRegistrationForm');
    $this->post('register', 'Auth\AuthController@register');



    //Admin routes
    Route::group(['as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth'], 'prefix' => 'beheer'], function() {

        Route::get('/', 'ManagementController@index');

        Route::get('/parking/new', 'ManagementController@newParking');
        Route::post('/parking/new', 'ManagementController@newParkingPost');

        Route::get('/parking/{id}', 'ManagementController@parking');
        Route::post('/parking/{id}/update', 'ManagementController@parkingUpdate');
        Route::get('/parking/{id}/remove', 'ManagementController@parkingRemove');

        //Blog stuff
        Route::get('/blog/new', 'BlogController@newBlogPost');
        Route::post('/blog/create', 'BlogController@create');

        Route::post('/blog/{id}/update', 'BlogController@blogUpdate');
        Route::get('/blog/{id}/remove', 'BlogController@remove');
        Route::get('/blog/{id}', 'BlogController@edit');



        Route::get('/klantenpaneel/', 'KlantenPaneelController@index');
        Route::get('/klantenpaneel/parking/{id}', 'KlantenPaneelController@parking');

    });

    Route::group(['prefix' => 'community'], function () {

        Route::get('/', 'CommunityController@index');

        Route::get('/toevoegen', 'CommunityController@toevoegen');
        Route::post('/toevoegen', 'CommunityController@toevoegenPost');

        Route::get('/lijst', 'CommunityController@lijst');

    });



    Route::get('/mindervaliden', 'ParkingController@mindervalidenstart');
    Route::get('/mindervaliden/{coords?}', 'ParkingController@mindervaliden');
    Route::post('/mindervaliden/', 'ParkingController@mindervaliden');


    Route::group(['prefix' => 'embed'], function () {

        Route::get('/', 'embedController@index');
        Route::get('/test', 'embedController@test');

    });


});



//Working with DataSources
Route::get('/update', 'ParkingController@enterData');


Route::group(['prefix' => 'api', 'middleware' => 'api'], function () {

    Route::get('/steden', 'ApiController@steden');
    Route::get('/stad/{stad}', 'ApiController@stad');

    Route::get('/parking/{parking}', 'ApiController@parking');
    Route::get('/parkings/lokatie/{lat}/{Lng}', 'ApiController@lokatie');
    Route::get('/parkings/{stad}', 'ApiController@parkings');

    Route::get('/vindStad/{lat}/{Lng}', 'ApiController@vindStad');

    Route::get('/twitter/{stad}', 'ApiController@twitter');

    Route::get('/chat/{stad}', 'ApiController@chat_stad');
    Route::get('/chat/{stad}/{parking}', 'ApiController@chat');

});


Route::get('/graph', 'ParkingController@graph');

Route::get('/twitter', 'TwitterController@start');


Route::get('/toevoegen', 'ParkingController@toevoegen');
Route::get('/toevoegen2', 'ParkingController@toevoegen2');
Route::post('/toevoegen', 'ParkingController@toevoegenPost');
Route::post('/toevoegen2', 'ParkingController@toevoegenPost2');


Route::post('/suggestie', 'ParkingController@suggestie');