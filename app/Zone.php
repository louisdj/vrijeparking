<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = "zones";

    public function zone_gebieden()
    {
        return $this->hasMany('App\Zone_gebied');
    }
}
