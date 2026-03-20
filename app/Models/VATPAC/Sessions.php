<?php

namespace App\Models\VATPAC;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Models\VATPAC\Users;

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

    public function vatpac_user()
    {
        return $this->belongsTo(Users::class, 'user', 'id');
    }
}
