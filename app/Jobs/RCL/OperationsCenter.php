<?php

namespace App\Jobs\RCL;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use App\Models\RCL\ATIS;
use App\Models\RCL\OnlinePilots;
use App\Models\RCL\Airports;
use App\Services\AviationWeatherClient;
use App\Services\HoppieClient;
use App\Services\VATSIMClient;

class OperationsCenter implements ShouldQueue
{
    use Queueable;

    protected array $operators;

    public function __construct()
    {
        $this->operators = [
            'BRITTANY WILLIAMS', 
            'ROB WRINGLER ', 
            'KEVIN NORMAN', 
            'DIXIE NORMOUS',
            'ANITA HARDCOK',
            'WILMA FINGERDOO',
            'LEE NOVER',
            'AMANDA HOUSE', 
            'RICHARD RUMP', 
            'BEN DOVER',
            'IVANA TINKLE',
            'PETER FILE',
        ];
    }


    public function handle(): void
    {
        // Initialise the server for Hoppie. Must be done once a minute to hold the callsign
        {
            $hoppieClient = new HoppieClient();
            $controllers = $hoppieClient->serverConnect();
        }



        // Check all VATSIM Connections for RCL Pilots
        {
            // $all_pilots = OnlinePilots::all();
            // foreach($all_pilots as $p){
            //     $p->online = 0;
            //     $p->save();
            // }

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

                    $elt_time = $this->eltCalculation($pilot->flight_plan->deptime, $pilot->flight_plan->enroute_time);
                    
                    $reg = [];
                    preg_match('/REG\/([A-Z0-9-]+)/', $pilot->flight_plan->remarks, $reg);

                    // OnlinePilots::UpdateorCreate(['callsign' => $pilot->callsign, 'dep' => $pilot->flight_plan->departure, 'arr' => $pilot->flight_plan->arrival,],
                    //     [
                    //         'name'          =>  preg_replace('/\s*[A-Z]{4}$/', '', $pilot->name),
                    //         'lat'           =>  $pilot->latitude,
                    //         'lon'           =>  $pilot->longitude,
                    //         'gs'            =>  $pilot->groundspeed,
                    //         'altn'          =>  $pilot->flight_plan->alternate ?? null,
                    //         'ralt'          =>  $ralt,
                    //         'eobt'          =>  $pilot->flight_plan->deptime,
                    //         'dep_distance'  =>  $dep_dist ?? null,
                    //         'arr_distance'  =>  $arr_dist ?? null,
                    //         'online'        =>  1,
                    //         'aircraft'      =>  $pilot->flight_plan->aircraft_short,
                    //         'registration'  =>  $reg[1] ?? null,
                    //         'route'         =>  $pilot->flight_plan->route,
                    //         'elt'           =>  $elt_time,
                    //         'fl'            =>  $pilot->flight_plan->altitude,
                    //     ]
                    // );
                }
            }
        }


        // Hoppie Connection Check ~ Every 5 Minutes
        $pilots = OnlinePilots::where('hoppie_connected', 0)->where('online', 1)->get();
        foreach($pilots as $pilot){
            $callsign = $hoppieClient->isConnected(($pilot->callsign));
            if($callsign == true){
                $pilot->hoppie_connected = 1;
                $pilot->save();
            }
        }


        // New Flight has been detected with Status = 0 & is connected to Hoppie
        // Send a TELEX to the Pilot advising their flight has been registered
        // Requires Status = 0 to activate
        {
            $pilots = OnlinePilots::where('hoppie_connected', 1)->where('online', 1)->get();
            foreach($pilots as $pilot){
                $operator = $this->operators[array_rand($this->operators)];
                $altn = $pilot->altn ?: 'NONE';
                $ralt = $pilot->ralt ?: 'NONE';

                $sentTelex = $hoppieClient->sendTelex($pilot->callsign, "
                    XXX {$pilot->callsign} CROC LOGON XXX
                    CPT: {$pilot->name}
                    ACFT: {$pilot->aircraft} {$pilot->registration}
                    RTE: {$pilot->route}
                    ALT: {$pilot->fl}
                    DEP: {$pilot->dep} {$pilot->eobt}z
                    ARR: {$pilot->arr} {$pilot->elt}z
                    ALTN: {$altn}
                    RALT: {$ralt}
                    DISPATCHER: {$operator}");

                $pilot->logon_time = Carbon::now();
                $pilot->status = 1;
                $pilot->hoppie_connected = 2;
                $pilot->save();

                // Aircraft has registered on hoppies in the air, so DEP weather needs to be skipped
                if($pilot->dep_distance > 3){
                    $pilot->status = 2;
                    $pilot->save();
                } else { //still on the ground. Need to monitor the ATIS
                    $pilot->dep_atis = 1;
                    $pilot->save();
                }
            }
        }


        // Aircraft is now 15mins from EOBT. Time to calculate the weather and send the information to the pilot
        // Requires Status = 1 to activate
        {
            $pilots = OnlinePilots::where('status', 1)->where('hoppie_connected', '!=', 0)->where('online', 1)->where('logon_time', '<=', Carbon::now()->addMinutes(2))->get();
            foreach($pilots as $pilot){

                $operator = $this->operators[array_rand($this->operators)];

                // Time to collect all the weather required for this message, and compile it in a ready to go message to be sent via telex.
                $weather = new AviationWeatherClient();
                $dep_weather = $weather->requestWeather($pilot->dep);
                $arr_weather = $weather->requestWeather($pilot->arr);
                $altn_weather = $weather->requestWeather($pilot->altn);

                $dep_metar  = $dep_weather['metar'] ?? 'METAR N/A';
                $dep_taf    = $dep_weather['taf'] ?? 'TAF N/A';
                $arr_metar  = $arr_weather['metar'] ?? 'METAR N/A';
                $arr_taf    = $arr_weather['taf'] ?? 'TAF N/A';
                $altn_metar = $altn_weather['metar'] ?? 'METAR N/A';
                $altn_taf   = $altn_weather['taf'] ?? 'TAF N/A';

                $dep_atis = ATIS::where('icao', $pilot->dep)->first();

                $blocks = [];
                $blocks[] = "WEATHER UPLINK FOR {$pilot->callsign},\n{$pilot->dep}-{$pilot->arr}, EOBT {$pilot->eobt}Z";
                
                // Departure block — use ATIS if online, otherwise METAR/TAF
                if ($dep_atis && $dep_atis->letter !== 'Offline') {
                    $atis_lines = json_decode($dep_atis->content, true);
                    $atis_text  = implode("\n", $atis_lines);
                    $blocks[] = "DEPARTURE ATIS: {$pilot->dep}";
                } else {
                    $monitoring = ($dep_atis && $dep_atis->letter === 'Offline')
                        ? "\nDEP ATIS OFFLINE - CROC MONITORING FOR CHANGES."
                        : '';
                    $blocks[] = "DEPARTURE WEATHER: {$pilot->dep}\n{$dep_metar}\n{$dep_taf}{$monitoring}";
                }

                $blocks[] = "ARRIVAL WEATHER: {$pilot->arr}\n{$arr_metar}\n{$arr_taf}";

                if($pilot->alternate !== null) {
                $blocks[] = "ALTERNATE WEATHER: {$pilot->altn}\n{$altn_metar}\n{$altn_taf}";
                }

                if($pilot->ralt !== null){
                $blocks[] = "ENROUTE ALTERNATES: {$pilot->ralt}";
                }

                $blocks[] = "RGRDS,\n{$operator}";

                $messages = [];
                $current  = '';

                foreach ($blocks as $block) {
                    $separator = $current === '' ? '' : "\n\n";
                    if (strlen($current . $separator . $block) <= 1000) {
                        $current .= $separator . $block;
                    } else {
                        if ($current !== '') {
                            $messages[] = $current;
                        }
                        $current = $block;
                    }
                }
                if ($current !== '') {
                    $messages[] = $current;
                }

                $total = count($messages);
                foreach ($messages as $i => $message) {
                    $num = $i + 1;
                    $prefix = $total > 1 ? "({$num}/{$total}) " : '';
                    $hoppieClient->sendTelex($pilot->callsign, $prefix . $message);
                    if ($i < $total - 1) {
                        sleep(3);
                    }
                }
                $pilot->status = 2;
                $pilot->save();
            }
        }


        // Watch the aircraft state to calculate an ETOPS Message to be sent to the aircraft ~ 45mins after departure
        // Requires Status = 2 to activate
        {
            $pilots = OnlinePilots::where('status', 2)->get();
            foreach($pilots as $pilot) {
                if($pilot->gs > 80 && $pilot->dep_distance > 3) {

                    // No Enroute Alternates? Skip this bit
                    if($pilot->ralt == null){
                        $pilot->status = 4;
                        $pilot->dep_atis = 0;
                        $pilot->last_atis_recieved = null;
                        $pilot->save();
                    } else {
                        $pilot->ralt_time = Carbon::now()->addMinutes(40);
                        $pilot->status = 3;
                        $pilot->dep_atis = 0;
                        $pilot->last_atis_recieved = null;
                        $pilot->save();
                    }
                }
            }
        }


        // Send an ETOPS message to the pilot if they have ETOPS alternates in their flight plan
        // Requires Status = 3 & ETOPS_Time !== null to activate
        {
            $pilots = OnlinePilots::where('status', 3)->where('online', 1)->whereNotNull('ralt_time')->where('ralt_time', '<=', Carbon::now())->get();
            // dd($pilots);
            $weather = new AviationWeatherClient();

            foreach($pilots as $pilot) {
                $ralt = explode(' ', $pilot->ralt);
                $raltWeather = [];

                if($pilot->ralt !== null){
                    foreach($ralt as $icao) {
                        $altn_weather = $weather->requestWeather($icao);
                        
                        $raltWeather[$icao] = [
                            'metar' => $altn_weather['metar'] ?? 'METAR N/A',
                            'taf'   => $altn_weather['taf'] ?? 'TAF N/A',
                        ];
                    }
                }

                $blocks = [];
                $blocks[] = "WEATHER ENROUTE ALTERNATES UPLINK FOR {$pilot->callsign}";

                if(count($raltWeather) > 0) {
                    foreach($raltWeather as $icao => $altENR) {
                        $blocks[] = "ENROUTE ALTERNATE: {$icao}\n{$altENR['metar']}\n{$altENR['taf']}";
                    }
                }

                $messages = [];
                $current  = '';

                foreach ($blocks as $block) {
                    $separator = $current === '' ? '' : "\n\n";
                    if (strlen($current . $separator . $block) <= 1000) {
                        $current .= $separator . $block;
                    } else {
                        if ($current !== '') {
                            $messages[] = $current;
                        }
                        $current = $block;
                    }
                }
                if ($current !== '') {
                    $messages[] = $current;
                }

                $total = count($messages);
                foreach ($messages as $i => $message) {
                    $num = $i + 1;
                    $prefix = $total > 1 ? "({$num}/{$total}) " : '';
                    $hoppieClient->sendTelex($pilot->callsign, $prefix . $message);
                    if ($i < $total - 1) {
                        sleep(3);
                    }
                }

                $pilot->status = 4;
                $pilot->ralt_time = null;
                $pilot->save();
            }
        }


        // Update Arrival / Alternate for Aircraft Arriving ~ 400NM from their arrival
        {
            $pilots = OnlinePilots::where('status', 4)->where('online', 1)->where('hoppie_connected', '!=', 0)->get();
            foreach($pilots as $pilot) {
                if($pilot->arr_distance < 400) {
                    $weather = new AviationWeatherClient();
                    $arr_weather = $weather->requestWeather($pilot->arr);
                    $altn_weather = $weather->requestWeather($pilot->altn);

                    $arr_metar  = $arr_weather['metar'] ?? 'METAR N/A';
                    $arr_taf    = $arr_weather['taf'] ?? 'TAF N/A';
                    $altn_metar = $altn_weather['metar'] ?? 'METAR N/A';
                    $altn_taf   = $altn_weather['taf'] ?? 'TAF N/A';

                    $arr_atis = ATIS::where('icao', $pilot->arr)->first();

                    $blocks = [];
                    $blocks[] = "ARRIVAL WEATHER UPLINK FOR {$pilot->callsign},\n{$pilot->dep}-{$pilot->arr}";
                    
                    // Arrival block — use ATIS if online, otherwise METAR/TAF
                    if ($arr_atis && $arr_atis->letter !== 'Offline') {
                        $atis_lines = json_decode($arr_atis->content, true);
                        $atis_text  = implode("\n", $atis_lines);
                        $blocks[] = "ARRIVAL ATIS: {$pilot->arr}";
                    } else {
                        $monitoring = ($arr_atis && $arr_atis->letter === 'Offline')
                            ? "\nARR ATIS OFFLINE - CROC MONITORING FOR CHANGES."
                            : '';
                        $blocks[] = "ARRIVAL WEATHER: {$pilot->arr}\n{$arr_metar}\n{$arr_taf}{$monitoring}";
                    }

                    if($pilot->alternate !== null) {
                        $blocks[] = "ALTERNATE WEATHER: {$pilot->altn}\n{$altn_metar}\n{$altn_taf}";
                    }

                    $messages = [];
                    $current  = '';

                    foreach ($blocks as $block) {
                        $separator = $current === '' ? '' : "\n\n";
                        if (strlen($current . $separator . $block) <= 1000) {
                            $current .= $separator . $block;
                        } else {
                            if ($current !== '') {
                                $messages[] = $current;
                            }
                            $current = $block;
                        }
                    }
                    if ($current !== '') {
                        $messages[] = $current;
                    }

                    $total = count($messages);
                    foreach ($messages as $i => $message) {
                        $num = $i + 1;
                        $prefix = $total > 1 ? "({$num}/{$total}) " : '';
                        $hoppieClient->sendTelex($pilot->callsign, $prefix . $message);
                        if ($i < $total - 1) {
                            sleep(3);
                        }
                    }

                    $pilot->status = 5;
                    $pilot->save();
                }
            }

        }


        // ATIS Monitoring Section - Within requirements so time to activate the mode
        {
            $pilots = OnlinePilots::where('status', 5)->where('online', 1)->where('hoppie_connected', '!=', 0)->where('arr_distance', '<', 400)->get();
            foreach($pilots as $pilot){
                $pilot->arr_atis = 1;
                $pilot->save();
            }
        }


        // Update with ATA message logoff message from Operations
        {
            $pilots = OnlinePilots::where('status', '>', 3)->where('status', '<', 10)->where('online', 1)->where('hoppie_connected', '!=', 0)->get();
            foreach( $pilots as $pilot ) {
                if($pilot->arr_distance < 3 && $pilot->gs < 81) {
                    $operator = $this->operators[array_rand($this->operators)];

                    $sentTelex = $hoppieClient->sendTelex($pilot->callsign, "
                    COMPLETION OF {$pilot->callsign},
                    {$pilot->dep}-{$pilot->arr}
                    CAPT: {$pilot->name}

                    Your flight has been recorded as having landed, and your flight has now been finalised with the CROC. See you next time.

                    Regards
                    {$operator}
                ");

                $pilot->arr_atis = 0;
                $pilot->last_atis_recieved = null;
                $pilot->status = 10;
                $pilot->save();
                }
            }
        }


        // ATIS Monitoring Section
        {
            // Departure ATIS
            $dep_pilots = OnlinePilots::where('dep_atis', 1)->where('hoppie_connected', 2)->where('online', 1)->where('logon_time', '<=', Carbon::now()->addMinutes(3))->get();
            foreach($dep_pilots as $p){
                $atis_info = ATIS::where('icao', $p->dep)->first();
                if($atis_info === null){
                    continue;
                }
                // dd($atis_info);
                
                // ATIS LETTER has changed --- send a Hoppie Message
                if($p->last_atis_recieved !== $atis_info->letter){

                    if($atis_info->letter == "Offline"){
                        $atis_section = "{$p->dep} ATIS NOW OFFLINE. \nCROC WILL ADVISE IF ATIS BECOMES AVAILABLE";
                    } else {
                        $atis_lines = json_decode($atis_info->content, true);
                        $atis_section = implode("\n", $atis_lines);
                    }

                    $hoppieClient = new HoppieClient();

                    $message = "{$p->callsign}, {$p->dep}-{$p->arr}";
                    $message .= "\n CROC MONITORING {$p->dep} ATIS";

                    $message .= "\n\n" . $atis_section;

                    $sentTelex = $hoppieClient->sendTelex($p->callsign, $message);


                    $p->last_atis_recieved = $atis_info->letter;
                    $p->save();
                }
            }

            // Arrival ATIS
            $arr_pilots = OnlinePilots::where('arr_atis', 1)->where('hoppie_connected', 2)->where('online', 1)->get();
            foreach($arr_pilots as $p){
                $atis_info = ATIS::where('icao', $p->arr)->first();
                if($atis_info === null){
                    continue;
                }
                // dd($atis_info);
                
                // ATIS LETTER has changed --- send a Hoppie Message
                if($p->last_atis_recieved !== $atis_info->letter){

                    if($atis_info->letter == "Offline"){
                        $atis_section = "{$p->arr} ATIS NOW OFFLINE. \nCROC WILL ADVISE IF ATIS BECOMES AVAILABLE.";
                    } else {
                        $atis_lines = json_decode($atis_info->content, true);
                        $atis_section = implode("\n", $atis_lines);
                    }

                    $hoppieClient = new HoppieClient();

                    $message = "{$p->callsign}, {$p->dep}-{$p->arr}";
                    $message .= "\n CROC MONITORING {$p->arr} ATIS";

                    $message .= "\n\n" . $atis_section;

                    $sentTelex = $hoppieClient->sendTelex($p->callsign, $message);


                    $p->last_atis_recieved = $atis_info->letter;
                    $p->save();
                }
            }
        }


        // Delete offline connections after 10 minutes
        {
            OnlinePilots::where('online', 0)->where('updated_at', '<=', Carbon::now()->subMinutes(60))->delete();
        }
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

    public function eltCalculation($dep, $enr)
    {
        $depHours = (int) substr($dep, 0, 2);
        $depMins  = (int) substr($dep, 2, 2);

        $routeHours = (int) substr($enr, 0, 2);
        $routeMins  = (int) substr($enr, 2, 2);

        $totalMinutes =
            ($depHours * 60 + $depMins) +
            ($routeHours * 60 + $routeMins);

        $elt = sprintf('%02d%02d', floor(($totalMinutes / 60) % 24), $totalMinutes % 60);
        return $elt;
    }
}
