<?php

namespace App\Models\VATPAC;
use App\Models\VATPAC\Sessions;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'vatpac_users';    

    protected $fillable = [
        'id',
    ];

    public function airportControllerTotals($icao, $cid, $rating)
    {
        
        $time = 0;

        if($rating >= 5){
            $sessions = Sessions::where('ICAO', $icao)->where('user', $cid)->whereBetween('rating', [5, 12])->whereNotNull('time_logged')->get();
        } else {
            $sessions = Sessions::where('ICAO', $icao)->where('user', $cid)->where('rating', $rating)->whereNotNull('time_logged')->get();
        }
        
        if($sessions->isEmpty()){
            $info = null;
            return $info;
        } else {
            $info = [];
        }
        
        foreach($sessions as $session){
            $time += $session->time_logged;
        }

        $h_time = CarbonInterval::seconds($time * 3600);
        $time_info = $h_time->cascade()->forHumans(['short' => true]);

        $info = [
            'time' => $time_info,
        ];

        return $info;
    }

    public function totalControllerTotals()
    {
        
    }
}
