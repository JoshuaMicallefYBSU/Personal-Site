<?php

namespace App\Http\Controllers\VATPAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VATPAC\Airports;
use App\Models\VATPAC\Users;
use App\Models\VATPAC\Sessions;


class VATPACController extends Controller
{
    public function ironMicView()
    {
        $airports = Airports::where('active', 1)
            ->orderBy('ICAO', 'asc')
            ->get();

        // Eager load users to avoid N+1 queries
        $sessions = Sessions::with('vatpac_user')
            ->where('still_connected', 0)
            ->get()
            ->groupBy('ICAO'); // 🔥 key optimisation

        $final_data = [];

        foreach ($airports as $airport) {

            $icao = $airport->ICAO;

            // Initialise structure ONCE per airport
            $final_data[$icao] = [
                'ratings' => [
                    'S1' => 0,
                    'S2' => 0,
                    'S3' => 0,
                    'C1+' => 0,
                ],
                'controllers' => [],
            ];

            // Skip if no sessions for this airport
            if (!isset($sessions[$icao])) {
                continue;
            }

            foreach ($sessions[$icao] as $session) {

                $cid = $session->user;
                $user = $session->vatpac_user; // relation

                // --- Calculate session duration in hours ---
                if ($session->logged_off) {
                    $seconds = strtotime($session->logged_off) - strtotime($session->logged_on);
                    $hours = $seconds / 3600;
                } else {
                    $hours = 0;
                }

                // --- Count rating ONLY once per controller ---
                    $rating = $user->rating;
                    if ($rating <= 2) {
                        $ratingLabel = 'S1';
                    } elseif ($rating == 3) {
                        $ratingLabel = 'S2';
                    } elseif ($rating == 4) {
                        $ratingLabel = 'S3';
                    } else {
                        $ratingLabel = 'C1+';
                    }

                // --- Initialise controller if not exists ---
                if (!isset($final_data[$icao]['controllers'][$cid])) {
                    $final_data[$icao]['controllers'][$cid] = [
                        'cid' => $cid,
                        'rating' => $ratingLabel,
                        'total_time' => 0,
                        'iron_mic' => 0, // placeholder
                        'sessions' => [],
                    ];

                    $final_data[$icao]['ratings'][$ratingLabel]++;
                }

                // --- Accumulate total time ---
                $final_data[$icao]['controllers'][$cid]['total_time'] += $hours;

                // --- Store session for future Iron Mic logic ---
                $final_data[$icao]['controllers'][$cid]['sessions'][] = [
                    'start' => $session->logged_on,
                    'end' => $session->logged_off,
                ];
            }

            // --- (Future) Iron Mic Calculation ---
            foreach ($final_data[$icao]['controllers'] as &$controller) {
                // dd($controller);
                $controller['iron_mic'] = $this->calculateIronMic($controller['sessions']);
            }

            // --- Sort controllers by iron mic (desc) ---
            usort($final_data[$icao]['controllers'], function ($a, $b) {
                return $b['iron_mic'] <=> $a['iron_mic'];
            });
        }

        $allSessions = Sessions::with('vatpac_user')
            ->where('still_connected', 0)
            ->orderBy('logged_on', 'asc')
            ->get();

        $totals_data = [
            'ratings' => [
                'S1' => 0,
                'S2' => 0,
                'S3' => 0,
                'C1+' => 0,
            ],
            'controllers' => [], // 👈 MUST BE ARRAY
        ];

        foreach ($allSessions as $session) {
            $cid = $session->user;
            $user = $session->vatpac_user;

            if (!$user) {
                continue;
            }

            $rating = $user->rating;

            if ($rating == 5) {
                $ratingLabel = 'S2';
            } elseif ($rating <= 1) {
                $ratingLabel = 'S1';
            } elseif ($rating == 2) {
                $ratingLabel = 'S2';
            } elseif ($rating == 3) {
                $ratingLabel = 'S3';
            } else {
                $ratingLabel = 'C1+';
            }

            if (!isset($totals_data['controllers'][$cid])) {
                $totals_data['controllers'][$cid] = [
                    'cid' => $cid,
                    'rating' => $ratingLabel,
                    'total_time' => 0,
                    'iron_mic' => 0,
                    'sessions' => [],
                ];

                $totals_data['ratings'][$ratingLabel]++;
            }

            if ($session->logged_off) {
                $seconds = strtotime($session->logged_off) - strtotime($session->logged_on);
                $hours = $seconds / 3600;

                $totals_data['controllers'][$cid]['total_time'] += $hours;

                $totals_data['controllers'][$cid]['sessions'][] = [
                    'start' => $session->logged_on,
                    'end' => $session->logged_off,
                ];
            }
        }

        foreach ($totals_data['controllers'] as &$controller) {
            $controller['iron_mic'] = $this->calculateIronMic($controller['sessions']);
        }
        unset($controller);

        $totals_data['controllers'] = array_values($totals_data['controllers']);

        usort($totals_data['controllers'], function ($a, $b) {
            return $b['iron_mic'] <=> $a['iron_mic'];
        });

        // dd($totals_data);

        $online = Sessions::where('still_connected',1)->orderBy('logged_on','asc')->get();

        return view('vatpac.events.ironmic-view', compact('airports', 'final_data', 'totals_data', 'online'));
    }

    private function calculateIronMic(array $sessions): float
    {
        $maxCountedInWindow = 4 * 3600; // 4 hours in seconds
        $windowSize = 8 * 3600;         // 8 hours in seconds
        $chunkSize = 60;                // process in 1-minute chunks for accuracy

        $intervals = [];

        // Normalise and sort sessions
        foreach ($sessions as $session) {
            if (empty($session['start']) || empty($session['end'])) {
                continue;
            }

            $start = $session['start'] instanceof \Carbon\CarbonInterface
                ? $session['start']->copy()->timestamp
                : strtotime($session['start']);

            $end = $session['end'] instanceof \Carbon\CarbonInterface
                ? $session['end']->copy()->timestamp
                : strtotime($session['end']);

            if ($start === false || $end === false || $end <= $start) {
                continue;
            }

            $intervals[] = [
                'start' => $start,
                'end'   => $end,
            ];
        }

        usort($intervals, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        /*
        * This stores ONLY time that has already been counted for Iron Mic.
        * Each element is an accepted counted chunk:
        * [
        *   'start' => timestamp,
        *   'end'   => timestamp,
        * ]
        */
        $countedSegments = [];

        $totalCountedSeconds = 0;

        foreach ($intervals as $interval) {
            $current = $interval['start'];
            $sessionEnd = $interval['end'];

            while ($current < $sessionEnd) {
                $windowStart = $current - $windowSize;

                // Remove counted segments that are completely outside the current 8h lookback window
                $countedSegments = array_values(array_filter($countedSegments, function ($segment) use ($windowStart) {
                    return $segment['end'] > $windowStart;
                }));

                // Calculate how much counted time already exists inside this rolling 8h window
                $countedInWindow = 0;

                foreach ($countedSegments as $segment) {
                    $overlapStart = max($segment['start'], $windowStart);
                    $overlapEnd = min($segment['end'], $current);

                    if ($overlapEnd > $overlapStart) {
                        $countedInWindow += ($overlapEnd - $overlapStart);
                    }
                }

                $remainingCapacity = $maxCountedInWindow - $countedInWindow;

                // No capacity left in this 8h window, move forward by one chunk
                if ($remainingCapacity <= 0) {
                    $current += $chunkSize;
                    continue;
                }

                // Work out the next chunk of session time we are considering
                $proposedEnd = min($current + $chunkSize, $sessionEnd);
                $proposedLength = $proposedEnd - $current;

                // Trim chunk if it would exceed remaining capacity
                if ($proposedLength > $remainingCapacity) {
                    $proposedEnd = $current + $remainingCapacity;
                    $proposedLength = $proposedEnd - $current;
                }

                if ($proposedLength <= 0) {
                    $current += $chunkSize;
                    continue;
                }

                // Accept this chunk as counted Iron Mic time
                $countedSegments[] = [
                    'start' => $current,
                    'end'   => $proposedEnd,
                ];

                $totalCountedSeconds += $proposedLength;
                $current = $proposedEnd;
            }
        }

        return round($totalCountedSeconds / 3600, 4);
    }
}
