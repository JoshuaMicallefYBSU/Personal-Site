<?php

namespace App\Models\VATPAC;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class Sessions extends Model
{
    protected $table = 'vatpac_sessions';    

    protected $fillable = [
        'callsign',
        'user',
        'rating',
        'ICAO',
        'logged_on',
        'logged_off',
        'still_connected',
        'time_logged',
    ];

    protected $casts = [
        'logged_on'  => 'datetime',
        'logged_off' => 'datetime',
        'still_connected' => 'boolean',
    ];

    public function loggedTime($logon)
    {
        // return $logon;
        return Carbon::parse($logon)->utc()->diffForHumans(
        now(),
        true,   // removes "ago"
        false,  // short syntax off (keeps words)
        2       // show 2 time parts (hour + minutes)
    );
    }
}
