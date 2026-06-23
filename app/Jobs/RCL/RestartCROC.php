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

class RestartCROC implements ShouldQueue
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

        $pilots = OnlinePilots::where('hoppie_connected', 2)->where('online', 1)->get();
        foreach($pilots as $pilot){
            // $sentTelex = $hoppieClient->sendTelex($pilot->callsign, "CROC SYSTEM ERROR......... SYSTEM REBOOTING DUE SERIOUS NETWORK ERROR. OPERATORS MAY EXPERIENCE REPEATED DUPLICATION MESSAGES WHILE SYSTEM IS RESTORED. ADVISE WILL BE GIVEN WHEN SYSTEM HAS RETURNED TO NORMAL OPERATIONS. REGARDS JOSHUA M");

            $sentTelex = $hoppieClient->sendTelex($pilot->callsign, "CROC SYSTEM RESTORED. SYSTEM WILL NOW OPERATE WITHIN NORMAL PARAMITERS. REGARDS JOSHUA M");
        }
    }
        
}
