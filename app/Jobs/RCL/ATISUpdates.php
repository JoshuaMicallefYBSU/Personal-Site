<?php

namespace App\Jobs\RCL;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use App\Models\RCL\OnlinePilots;
use App\Models\RCL\Airports;
use App\Models\RCL\ATIS;
use App\Services\AviationWeatherClient;
use App\Services\HoppieClient;
use App\Services\VATSIMClient;

class ATISUpdates implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        
    }


    public function handle(): void
    {
        $vatsimData = new VATSIMClient();
        $pilots = $vatsimData->getRCLPilots();

        $airport_icaos = [];
            
        // Set all ATIS' to offline status, so we can then itterate after
        $atiss = ATIS::all();
        foreach($atiss as $atis){
            $atis->aircraft_inbound = 0;
            $atis->save();
        }


        // Check which ATIS' need to be monitored
        {
            $pilots = OnlinePilots::where('arr_distance', '<', 300)->where('hoppie_connected', '!=', 0)->get();
            foreach($pilots as $pilot){
                ATIS::updateOrCreate(
                    ['icao' => $pilot->arr],  
                    ['aircraft_inbound' => 1]   
                );
            }
        }

        
        // Itterate throught each ATIS that needs to be monitored and update it accordingly.
        {
            $atiss = ATIS::where('aircraft_inbound', 1)->get();
            foreach($atiss as $atis){
                $vatsimATIS = new VATSIMClient;
                $network = $vatsimATIS->findATIS($atis->icao);

                if($network['text'] === $atis->content){
                    continue;
                }

                $atis->change_detected = 1;
                $atis->letter = $network['letter'];
                $atis->content = $network['text'];
                $atis->save();
            }
        }


        // Itterate through each ATIS that has changes, and send it off to all users.
        {
            $changes = ATIS::where('change_detected', 1)->get();
            foreach($changes as $a){
                $arr = $a->icao;
                $pilots = OnlinePilots::where('arr', $arr)->where('arr_distance', '<', 300)->where('hoppie_connected', '!=', 0)->where('online', 1)->get();

                if($a->content == null){
                    $atis_section = "NO ATIS CURRENTLY AVAILABLE. \nCROC NOW MONITORING AND WILL ADVISE OF ANY STATUS CHANGE.";
                } else {
                    $atis_lines = json_decode($a->content, true);
                    $atis_section = implode("\n", $atis_lines);
                }
                
                foreach($pilots as $pilot){


                    $hoppieClient = new HoppieClient();

                    $message = "{$pilot->callsign}, {$pilot->dep}-{$pilot->arr}";
                    $message .= "\n MONITORING ATIS FOR {$pilot->arr}";

                    $message .= "\n\n" . $atis_section;

                    $sentTelex = $hoppieClient->sendTelex($pilot->callsign, $message);
                }

                $a->change_detected = 0;
                $a->save();
            }
        }
    }
}
