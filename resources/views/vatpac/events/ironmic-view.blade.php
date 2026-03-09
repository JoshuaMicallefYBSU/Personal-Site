@extends('vatpac.layout')

@section('content')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="VATPAC - Iron Mic Leaderboard">
    <meta property="og:description"
        content="Event Position Tracker for the Iron Mic hosted by VATPAC in 2026">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

<p>See all the airports participating in the VATPAC Iron Mic Event occuring between the 20th-23rd of March, 2026!/p>

{{-- Airport Views --}}
<div class="row">
    @foreach($airports as $airport)

        <div class="col-md-6">
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="card-title">{{$airport->ICAO}} - {{$airport->Name}}</h4>
                        </div>

                        <div class="col-md-3" style="font-size: 10px;">
                            <u>AD Callsign</u><br>{{$airport->aerodrome_regex}}
                        </div>

                        <div class="col-md-3" style="font-size: 10px;">
                            <u>ENR Callsign</u><br>{{$airport->enroute_regex}}
                        </div>
                    </div>
                    
                    

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="controllers-tab" data-toggle="tab" href="#{{$airport->ICAO}}-s1">S1</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="positions-tab" data-toggle="tab" href="#{{$airport->ICAO}}-s2">S2</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="facility-tab" data-toggle="tab" href="#{{$airport->ICAO}}-s3">S3</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="facility-tab" data-toggle="tab" href="#{{$airport->ICAO}}-other">C1/C3/I1/I3</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="facility-tab" data-toggle="tab" href="#{{$airport->ICAO}}-totals">Totals</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="{{$airport->ICAO}}-s1">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)                                            
                                        @php
                                            $rating = 2;
                                            $details = $user->airportControllerTotals($airport->ICAO, $user->id, $rating);
                                        @endphp

                                        {{-- Exit Loop if no sessions --}}
                                        @if($details == null)
                                            @continue;
                                        @endif

                                        <tr>  
                                            <td>{{$user->id}}</td>
                                            <td>{{$details['time']}}</td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade show" id="{{$airport->ICAO}}-s2">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)                                            
                                        @php
                                            $rating = 3;
                                            $details = $user->airportControllerTotals($airport->ICAO, $user->id, $rating);
                                        @endphp

                                        {{-- Exit Loop if no sessions --}}
                                        @if($details == null)
                                            @continue;
                                        @endif

                                        <tr>  
                                            <td>{{$user->id}}</td>
                                            <td>{{$details['time']}}</td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade show" id="{{$airport->ICAO}}-s3">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>  
                                        @foreach($users as $user)                                            
                                        @php
                                            $rating = 4;
                                            $details = $user->airportControllerTotals($airport->ICAO, $user->id, $rating);
                                        @endphp

                                        {{-- Exit Loop if no sessions --}}
                                        @if($details == null)
                                            @continue;
                                        @endif

                                        <tr>  
                                            <td>{{$user->id}}</td>
                                            <td>{{$details['time']}}</td>
                                            <td></td>
                                        </tr>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade show" id="{{$airport->ICAO}}-other">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>  
                                        @foreach($users as $user)                                            
                                        @php
                                            $rating = 5;
                                            $details = $user->airportControllerTotals($airport->ICAO, $user->id, $rating);
                                        @endphp

                                        {{-- Exit Loop if no sessions --}}
                                        @if($details == null)
                                            @continue;
                                        @endif

                                        <tr>  
                                            <td>{{$user->id}}</td>
                                            <td>{{$details['time']}}</td>
                                            <td></td>
                                        </tr>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane fade show" id="{{$airport->ICAO}}-totals">
                            <table class="table" style="text-align: center; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {{-- <tr>  
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr> --}}
                                    <tr>
                                        <td>Totals Currently Disabled - Coming soon</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endforeach
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title">Controller Totals</h4>
                <p>This section is presently disabled. Coming in a future update</p>

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="controllers-tab" data-toggle="tab" href="#s1">S1</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="positions-tab" data-toggle="tab" href="#s2">S2</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="facility-tab" data-toggle="tab" href="#s3">S3</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="facility-tab" data-toggle="tab" href="#other">C1/C3/I1/I3</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="facility-tab" data-toggle="tab" href="#totals">Totals</a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="s1">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">CID / Name</th>
                                <th width="30%">Total Time</th>
                                <th width="30%">Iron Mic Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade show" id="s2">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">CID / Name</th>
                                <th width="30%">Total Time</th>
                                <th width="30%">Iron Mic Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade show" id="s3">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">CID / Name</th>
                                <th width="30%">Total Time</th>
                                <th width="30%">Iron Mic Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade show" id="other">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">CID / Name</th>
                                <th width="30%">Total Time</th>
                                <th width="30%">Iron Mic Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade show" id="totals">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">CID / Name</th>
                                <th width="30%">Total Time</th>
                                <th width="30%">Iron Mic Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>  
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title">Currently Connected</h4>
                <p>Shows a complete list of all current connected users</p>

                    <div class="tab-pane fade show" id="totals">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                            <tr>
                                <th width="40%">Callsign</th>
                                <th width="30%">CID</th>
                                <th width="30%">Time</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($online as $session)
                                <tr>  
                                    <td>{{$session->callsign}}</td>
                                    <td>{{$session->user}}</td>
                                    <td>{{$session->loggedTime($session->logged_on)}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection