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

    public $timestamps = false;
}
