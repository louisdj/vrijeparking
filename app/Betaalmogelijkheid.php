<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Betaalmogelijkheid extends Model
{

    public function Betaalmiddel()
    {
        return $this->hasOne('App\Betaalmiddel');
    }

    public $timestamps = false;

    protected $table = 'parking_betaalmogelijkheden';
}
