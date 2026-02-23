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

    public function getControllers()
    {
    $data = $this->getVATSIMData();

    return $data->controllers ?? []; // Ensures it always returns an array
    }
}