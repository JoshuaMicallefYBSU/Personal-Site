<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VATPAC\Airports;

class VATPACController extends Controller
{
    public function ironMicView()
    {
        $airports = Airports::where('active', 1)->orderBy('ICAO', 'asc')->get();

        return view('vatpac.events.ironmic-view', compact('airports'));
    }
}
