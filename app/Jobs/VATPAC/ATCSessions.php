<?php

namespace App\Jobs\VATPAC;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use App\Models\VATPAC\Airports;
use App\Models\VATPAC\Sessions;
use App\Models\VATPAC\Users;
use App\Services\VATSIMClient;

class ATCSessions implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $airports = Airports::where('active', 1)->get();

        // VATSIM data
        $vatsimData = new VATSIMClient();
        $controllers = $vatsimData->getControllers();

        // Build allowed position prefixes
        $positions = [];
        $positionToIcao = [];

        foreach ($airports as $airport) {
            foreach (['aerodrome_regex', 'enroute_regex'] as $field) {
                if (empty($airport->{$field})) {
                    continue;
                }

                foreach (explode(',', $airport->{$field}) as $value) {
                    $prefix = trim($value);

                    $positions[] = $prefix;
                    $positionToIcao[$prefix] = $airport->ICAO; // <-- store which airport owns this prefix
                }
            }
        }

        // Track which sessions are live this run (by their natural key)
        $liveKeys = [];

        foreach ($controllers as $controller) {

            if ($controller->facility == 0 || $controller->facility == 1 || $controller->rating == 1) {
                continue;
            }

            $prefix = explode('_', $controller->callsign)[0];

            // if no mapping exists, ignore
            if (!isset($positionToIcao[$prefix])) {
                continue;
            }

            $icao = $positionToIcao[$prefix];

            $loggedOnUtc = Carbon::parse($controller->logon_time)->utc();

            Users::updateOrCreate(
                ['id' => $controller->cid],
                ['rating' => $controller->rating]
            );

            Sessions::updateOrCreate(
                [
                    'callsign'  => $controller->callsign,
                    'user'      => $controller->cid,
                    'logged_on' => $loggedOnUtc->toDateTimeString(),
                ],
                [
                    'rating'          => $controller->rating,
                    'ICAO'            => $icao,     // ✅ correct airport for this callsign prefix
                    'still_connected' => true,
                    'logged_off'      => null,
                    'time_logged'     => null,
                ]
            );

            $liveKeys[] = [
                'callsign'  => $controller->callsign,
                'user'      => $controller->cid,
                'logged_on' => $loggedOnUtc->toDateTimeString(),
            ];
        }

        // ---- Disconnect anything that WAS connected but is NOT in the liveKeys list ----
        // If nothing live, disconnect everything still_connected = 1
        if (empty($liveKeys)) {
            Sessions::where('still_connected', 1)->update(['still_connected' => 0]);
        } else {
            // Mark as disconnected any currently connected sessions not seen this run.
            // We do it in PHP because MySQL "where not in composite tuple" is awkward.
            $currentlyConnected = Sessions::where('still_connected', 1)->get();

            $liveLookup = collect($liveKeys)->map(function ($k) {
                return "{$k['callsign']}|{$k['user']}|{$k['logged_on']}";
            })->flip();

            foreach ($currentlyConnected as $cs) {
                $key = "{$cs->callsign}|{$cs->user}|{$cs->logged_on->copy()->utc()->toDateTimeString()}";

                if (!$liveLookup->has($key)) {
                    $cs->still_connected = 0;
                    $cs->save();
                }
            }
        }

        // ---- Close sessions that are now disconnected and not yet closed ----
        $nowUtc = now()->utc();

        $disconnect_sessions = Sessions::where('still_connected', 0)
            ->whereNull('logged_off')
            ->get();

        foreach ($disconnect_sessions as $ds) {
            $loggedOn = $ds->logged_on instanceof Carbon
                ? $ds->logged_on->copy()->utc()
                : Carbon::parse($ds->logged_on)->utc();

            $minutes = $loggedOn->diffInMinutes($nowUtc);

            $ds->logged_off = $nowUtc;
            $ds->time_logged = round($minutes / 60, 2);
            $ds->save();
        }

        // remove dd in production, it will halt your worker
        // dd($controllers);
    }
}
