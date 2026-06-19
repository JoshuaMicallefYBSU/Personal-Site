<?php

namespace App\Models\RCL;

use Illuminate\Database\Eloquent\Model;

class Airports extends Model
{
    protected $table = 'rcl_airports';    

    protected $fillable = [
        'id',
        'icao',
        'name',
        'latitude',
        'longitude'
    ];
}
