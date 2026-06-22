<?php

namespace App\Models\RCL;

use Illuminate\Database\Eloquent\Model;

class OnlinePilots extends Model
{
    protected $table = 'rcl_online_pilots';    

    protected $fillable = [
        'id',
        'callsign',
        'name',
        'lat',
        'lon',
        'gs',
        'dep',
        'arr',
        'altn',
        'ralt',
        'eobt',
        'dep_distance',
        'arr_distance',
        'hoppie_connected',
        'status',
        'online',
        'aircraft',
        'registration',
        'route',
        'elt',
        'fl',
    ];
}
