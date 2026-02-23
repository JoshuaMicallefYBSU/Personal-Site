<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VATPAC\Airports;
use App\Models\VATPAC\Users;
use App\Models\VATPAC\Sessions;


class VATPACController extends Controller
{
    public function ironMicView()
    {
        $airports = Airports::where('active', 1)->orderBy('ICAO', 'asc')->get();
        $users = Users::all();
        $online = Sessions::where('still_connected',1)->orderBy('logged_on','asc')->get();

        return view('vatpac.events.ironmic-view', compact('airports', 'users', 'online'));
    }
}
