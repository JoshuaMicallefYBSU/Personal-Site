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
        $weather = [];
        try {
            $metar = $this->client->get("metar?ids={$icao}");
            $taf = $this->client->get("taf?ids={$icao}");

            $weather = [
                'metar' => (string) $metar->getBody() ?? 'METAR N/A',
                'taf'   => (string) $taf->getBody() ?? 'TAF N/A',
            ];

            return $weather;
        } catch (GuzzleException $e) {

        }
    }
}
