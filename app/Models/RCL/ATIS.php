<?php

namespace App\Models\RCL;

use Illuminate\Database\Eloquent\Model;

class ATIS extends Model
{
    protected $table = 'rcl_atis';    

    protected $fillable = [
        'id',
        'icao',
        'letter',
        'content',
        'offline',
        'aircraft_inbound',
        'change_detected',
    ];
}
