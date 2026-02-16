<?php

namespace App\Models\VATPAC;

use Illuminate\Database\Eloquent\Model;

class Airports extends Model
{
    protected $table = 'vatpac_airports';    

    protected $fillable = [
        'name',
        'rating',
    ];

    public function FlightInfo()
    {
        return $this->belongsTo(Flights::class, 'callsign', 'id');
    }
}
