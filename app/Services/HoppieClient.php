<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class HoppieClient
{
    protected Client $client;
    protected string $logon;

    public function __construct()
    {
        $this->logon = env('HOPPIE_LOGON');
        $this->callsign = 'RCLOPS';

        $this->client = new Client([
            'base_uri' => 'http://www.hoppie.nl',
            'timeout'  => 5,
        ]);
    }

    // Run the script to register RCLOPS as a connected station on Hoppie
    public function serverConnect()
    {
        try {
            $response = $this->client->get('/acars/system/connect.html', [
                'query' => [
                    'logon'  => $this->logon,
                    'from'   => $this->callsign,
                    'to'     => 'SERVER',                 // MUST be your own station
                    'type'   => 'poll',
                    'packet' => '',    // callsign(s) go here
                ],
            ]);

        } catch (GuzzleException $e) {
            Log::warning('Hoppie station ping failed', [
                'callsign' => $callsign,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if a callsign is connected to Hoppie
     */
    public function isConnected(string $callsign, $from): bool
    {
        try {
            $response = $this->client->get('/acars/system/connect.html', [
                'query' => [
                    'logon'  => $this->logon,
                    'from'   => 'SERVER',
                    'to'     => strtoupper($from),                 // MUST be your own station
                    'type'   => 'ping',
                    'packet' => strtoupper($callsign),    // callsign(s) go here
                ],
            ]);

            $body = trim((string) $response->getBody());

            // If the callsign appears in the response, it is online
            return str_contains($body, strtoupper($callsign));

        } catch (GuzzleException $e) {
            Log::warning('Hoppie station ping failed', [
                'callsign' => $callsign,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }
    
    public function sendTelex(string $to, string $message)
    {
        // TELEX Message
        try {
            $response = $this->client->get('/acars/system/connect.html', [
                'query' => [
                    'logon'  => $this->logon,
                    'from'   => $this->callsign,
                    'to'     => strtoupper($to),
                    'type'   => 'telex',
                    'packet' => $message,
                ],
            ]);

        } catch (GuzzleException $e) {
            Log::error('Hoppie telex send failed', [
                'from'  => $this->callsign,
                'to'    => strtoupper($to),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convenience method:
     * Ping first, then send if connected
     */
    public function sendIfConnected(string $from, string $to, string $message): bool
    {
        if (! $this->isConnected($to)) {
            return false;
        }

        return $this->sendTelex($from, $to, $message);
    }
}
