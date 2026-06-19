<?php

namespace App\Jobs\RCL;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use App\Models\RCL\OnlinePilots;
use App\Models\RCL\Airports;
use App\Services\HoppieClient;
use App\Services\VATSIMClient;

class OperationsCenter implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Initialise the server for Hoppie. Must be done once a minute to hold the callsign
        $hoppieClient = new HoppieClient();
        $controllers = $hoppieClient->serverConnect();



        // Check all VATSIM Connections for RCL Pilots
        $vatsimData = new VATSIMClient();
        $pilots = $vatsimData->getRCLPilots();

        foreach($pilots as $pilot){
            $ralt = null;

            // Only care about the aircraft if the flight plan exists
            if($pilot->flight_plan !== null){


                $remarks = $pilot->flight_plan->remarks;
                preg_match('/RALT\/([^\s\/][^\/]*)/', $remarks, $matches);
                $ralt = isset($matches[1]) ? trim($matches[1]) : null;

                $dep = Airports::where('icao', $pilot->flight_plan->departure)->first();
                $arr = Airports::where('icao', $pilot->flight_plan->arrival)->first();

                $dep_dist = $this->calculateDistance($pilot->latitude, $pilot->longitude, $dep->latitude, $dep->longitude);
                $arr_dist = $this->calculateDistance($pilot->latitude, $pilot->longitude, $arr->latitude, $arr->longitude);

                OnlinePilots::UpdateorCreate(['callsign' => $pilot->callsign, 'dep' => $pilot->flight_plan->departure, 'arr' => $pilot->flight_plan->arrival,],
                    [
                        'name'  =>  preg_replace('/\s*[A-Z]{4}$/', '', $pilot->name),
                        'lat'   =>  $pilot->latitude,
                        'lon'   =>  $pilot->longitude,
                        'gs'    =>  $pilot->groundspeed,
                        'altn'  =>  $pilot->flight_plan->alternate,
                        'ralt'          =>  $ralt,
                        'eobt'          =>  $pilot->flight_plan->deptime,
                        'dep_distance'  =>  $dep_dist ?? null,
                        'arr_distance'  =>  $arr_dist ?? null,
                    ]
                );
            }

            
        }






        // Send a message
        // $sentTelex = $hoppieClient->sendTelex('RCL7', '
            // WEATHER UPLINK FOR RCL7,
            // XXXX-XXXX, EOBT XXXX
            // CAPT: Joshua M

            // DEP WEATHER: XXXX
            // METAR : N/A
            // TAF: N/A

            // ARR WEATHER: XXXX
            // METAR : N/A
            // TAF: N/A

            // ALTN WEATHER: XXXX
            // METAR: N/A 
            // TAF: N/A

            // ENR ALTN 1 WEATHER: XXXX
            // METAR: N/A 
            // TAF: N/A

            // ENR ALTN 2 WEATHER: XXXX
            // METAR: N/A 
            // TAF: N/A
            // ');
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) 
    {
        $earthRadiusNm = 3443.920; // Radius of Earth in nautical miles
    
        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);
    
        // Calculate the differences
        $latDifference = $lat2Rad - $lat1Rad;
        $lonDifference = $lon2Rad - $lon1Rad;
    
        // Apply Haversine formula
        $a = sin($latDifference / 2) ** 2 + cos($lat1Rad) * cos($lat2Rad) * sin($lonDifference / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceNm = $earthRadiusNm * $c;
        return $distanceNm;
    }
}
