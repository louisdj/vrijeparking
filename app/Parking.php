<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
//    public function afstand($mapcenter) {
//        $distance = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins=' . $mapcenter . "&destinations=". $this->latitude . "," . $this->longitude . "&mode=walking&language=fr-FR&key=AIzaSyAwXAdR81t0uD5Y65HJE6IO9Ezx5ZVFBIo"));
//        $distanceResult = ($distance->rows[0]->elements[0]);
//
//        $afstand = $distanceResult->distance->text;
//        $tijdsduur = $distanceResult->duration->text;
//
//        dd($afstand);
//    }

    public function openingsuren()
    {
        return $this->hasMany('App\Openingsuren');
    }

    public function betaalmogelijkheden()
    {
        return $this->hasMany('App\Betaalmogelijkheid');
    }

    public function tarieven()
    {
        return $this->hasMany('App\Tarief', 'parking_id', 'id');
    }

    protected $table = 'parkings';
}
