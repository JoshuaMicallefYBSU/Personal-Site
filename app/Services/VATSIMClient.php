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

    public function getATISMData()
    {
        $client = new Client();
        $response = $client->get('https://data.vatsim.net/v3/afv-atis-data.json');

        if ($response->getStatusCode() === 200) {
            return json_decode($response->getBody(), true);
        }

        return [];
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

    public function findATIS($icao)
    {
        $atis = $this->getATISMData();

        $info = [
            'letter'    => 'Offline',
            'text'      => 'Offline',
        ];

        foreach($atis as $a){
            if(preg_match('/^' . $icao . '_(?!D_)[A-Z]?_?ATIS$/', $a['callsign'])){
                $info['letter'] = $a['atis_code'];
                $info['text']   = json_encode($a['text_atis']);
                break;
            }
        }

        return $info;
    }
    
    
}