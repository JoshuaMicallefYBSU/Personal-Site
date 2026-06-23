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
        // Set all ATIS' to offline status, so we can then iterate after
        ATIS::query()->update(['aircraft_inbound' => 0]);

        // Check which ATIS' need to be monitored (arr AND dep within range)
        $pilots = OnlinePilots::all();

        foreach ($pilots as $pilot) {
            if ($pilot->arr) {
                ATIS::updateOrCreate(
                    ['icao' => $pilot->arr],
                    [
                        'aircraft_inbound'  => 1,
                    ]
                );
            }

            if ($pilot->dep) {
                ATIS::updateOrCreate(
                    ['icao' => $pilot->dep],
                    [
                        'aircraft_inbound' => 1,
                    ]
                );
            }
        }

        // Iterate through each ATIS that needs to be monitored and update it accordingly
        $atiss = ATIS::where('aircraft_inbound', 1)->get();
        foreach ($atiss as $atis) {
            $vatsimATIS = new VATSIMClient;
            $network = $vatsimATIS->findATIS($atis->icao);

            // Connected & No Update
            if ($network['text'] === $atis->content) {
                $atis->letter = $network['letter'];
                $atis->content = $network['text'];
                $atis->save();
                continue;
            }

            // Connected & Updated
            if ($network['text'] !== $atis->content) {
                $atis->change_detected = 1;
                $atis->letter = $network['letter'];
                $atis->content = $network['text'];
                $atis->save();
                continue;
            }

            // Offline
            if($network['text'] === "Offline"){
                $atis->offline = 1;
                $atis->letter = "Offline";
                $atis->content = "Offline";
                $atis->save();
                continue;
            }
        }

        // Delete irrelevant ATIS' after 10 minutes
        ATIS::where('aircraft_inbound', 0)
            ->where('updated_at', '<=', Carbon::now()->subMinutes(10))
            ->delete();
    }
}