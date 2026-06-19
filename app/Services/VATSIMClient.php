<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class VATSIMClient
{
    public function getVATSIMData()
    {
        $client = new Client();
        $responseStatus = $client->get('https://status.vatsim.net/status.json');
        $dataUrl = json_decode($responseStatus->getBody())->data->v3[0];

        $response = $client->get($dataUrl);

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody());
        }
    }

    public function getRCLPilots()
    {
        $rclPilots = [];

        $pilots = $this->getVATSIMData();
        $pilots = $pilots->pilots;

        foreach($pilots as $pilot){
            if($pilot->callsign[0] === 'R' && $pilot->callsign[1] === 'C' && $pilot->callsign[2] === 'L'){
                $rclPilots[] = $pilot;
            }
        }

        return $rclPilots;
    }
    
}