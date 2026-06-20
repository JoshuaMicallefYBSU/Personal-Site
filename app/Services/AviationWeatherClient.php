<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AviationWeatherClient
{
    protected Client $client;
    protected string $logon;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://aviationweather.gov/api/data/',
            'timeout'  => 5,
        ]);
    }

    // Run the script to register RCLOPS as a connected station on Hoppie
    public function requestWeather($icao)
    {
        $metar = 'METAR N/A';
        $taf   = 'TAF N/A';

        try {
            $response = (string) $this->client->get("metar?ids={$icao}")->getBody();
            if (!empty(trim($response))) {
                $metar = trim($response);
            }
        } catch (GuzzleException $e) {
            Log::error("AviationWeatherClient METAR error for {$icao}: " . $e->getMessage());
        }

        try {
            $response = (string) $this->client->get("taf?ids={$icao}")->getBody();
            if (!empty(trim($response))) {
                $taf = trim($response);
            }
        } catch (GuzzleException $e) {
            Log::error("AviationWeatherClient TAF error for {$icao}: " . $e->getMessage());
        }

        return [
            'metar' => $metar,
            'taf'   => $taf,
        ];
    }
}
