<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Betaalmogelijkheid extends Model
{

    public function betaalmiddel()
    {
        return $this->hasOne('App\Betaalmiddel', 'id', 'betaalmiddel_id');
    }

    public $timestamps = false;

    protected $table = 'parking_betaalmogelijkheden';
}
