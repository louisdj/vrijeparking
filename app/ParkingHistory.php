<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParkingHistory extends Model
{
    protected $table = "parkings_historie";

    protected $fillable = [
        'parking_id',
        'bezetting',
    ];

    public $timestamps = false;
}
