@extends('vatpac.layout')

@section('content')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="VATPAC - Iron Mic Leaderboard">
    <meta property="og:description"
        content="Event Position Tracker for the Iron Mic hosted by VATPAC in 2026">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

<p>See all the airports participating in the VATPAC Iron Mic Event occuring between the 20th-23rd of March, 2026!</p>

{{-- Airport Views --}}
<div class="row">
    @foreach($airports as $airport)
    @php
        $airportData = $final_data[$airport->ICAO] ?? [
            'ratings' => [
                'S1' => 0,
                'S2' => 0,
                'S3' => 0,
                'C1+' => 0,
            ],
            'controllers' => [],
        ];

        $controllers = $airportData['controllers'];
    @endphp

    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="card-title">{{ $airport->ICAO }} - {{ $airport->Name }}</h4>
                    </div>

                    <div class="col-md-3" style="font-size: 10px;">
                        <u>AD Callsign</u><br>{{ $airport->aerodrome_regex }}
                    </div>

                    <div class="col-md-3" style="font-size: 10px;">
                        <u>ENR Callsign</u><br>{{ $airport->enroute_regex }}
                    </div>
                </div>

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#{{ $airport->ICAO }}-s1">
                            S1 ({{ $airportData['ratings']['S1'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#{{ $airport->ICAO }}-s2">
                            S2 ({{ $airportData['ratings']['S2'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#{{ $airport->ICAO }}-s3">
                            S3 ({{ $airportData['ratings']['S3'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#{{ $airport->ICAO }}-other">
                            C1/C3/I1/I3 ({{ $airportData['ratings']['C1+'] ?? 0 }})
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- S1 --}}
                    <div class="tab-pane fade show active" id="{{ $airport->ICAO }}-s1">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($controllers as $controller)
                                    @if($controller['rating'] !== 'S1')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- S2 --}}
                    <div class="tab-pane fade" id="{{ $airport->ICAO }}-s2">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($controllers as $controller)
                                    @if($controller['rating'] !== 'S2')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- S3 --}}
                    <div class="tab-pane fade" id="{{ $airport->ICAO }}-s3">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($controllers as $controller)
                                    @if($controller['rating'] !== 'S3')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- C1+ --}}
                    <div class="tab-pane fade" id="{{ $airport->ICAO }}-other">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($controllers as $controller)
                                    @if($controller['rating'] !== 'C1+')
                                        @continue
                                    @endif
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
</div>

<br>
<hr>

<div class="row">
    <div class="col-md-6">
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title">Controller Totals</h4>
                <p>See the total hours connected, and accepted during the Iron Mic!</p>

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#s1">
                            S1 ({{ $totals_data['ratings']['S1'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#s2">
                            S2 ({{ $totals_data['ratings']['S2'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#s3">
                            S3 ({{ $totals_data['ratings']['S3'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#other">
                            C1/C3/I1/I3 ({{ $totals_data['ratings']['C1+'] ?? 0 }})
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#totals">
                            Total ({{ $totals_data['ratings']['all'] ?? 0 }})
                        </a>
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
                                @forelse($totals_data['controllers']['S1'] as $controller)
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="s2">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totals_data['controllers']['S2'] as $controller)
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="s3">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totals_data['controllers']['S3'] as $controller)
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="other">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="40%">CID / Name</th>
                                    <th width="30%">Total Time</th>
                                    <th width="30%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totals_data['controllers']['C1+'] as $controller)
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="totals">
                        <table class="table" style="text-align: center; font-size: 12px;">
                            <thead>
                                <tr>
                                    <th width="30%">CID / Name</th>
                                    <th width="30">Rating</th>
                                    <th width="20%">Total Time</th>
                                    <th width="20%">Iron Mic Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totals_data['all'] as $controller)
                                    <tr>
                                        <td>{{ $controller['cid'] }}</td>
                                        <td>{{$controller['rating']}}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['total_time'] * 3600))->format('G:i') }}</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestampUTC(round($controller['iron_mic'] * 3600))->format('G:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No controllers found</td>
                                    </tr>
                                @endforelse
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
                                    <td>{{ \Carbon\Carbon::parse($session->logged_on)->diff(\Carbon\Carbon::now())->format('%H:%I') }}</td>
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