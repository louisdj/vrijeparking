<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stad extends Model
{
    protected $table = 'data_sources';

    protected $hidden = [
        'url',
        'fetch_data',
        'fetch_class',
    ];

    public function aantal_parkings()
    {
        return Parking::where('stad', strtolower($this->stad))->get()->count();
    }

    public function parkings()
    {
        return $this->hasMany('App\Parking', 'stad', 'stad');
    }

    public function totaal_plaatsen()
    {
        $plaatsen = 0;

        foreach($this->parkings as $parking) {
            $plaatsen += $parking->totaal_plaatsen;
        }

        return $plaatsen;
    }

    public $timestamps = false;
}
