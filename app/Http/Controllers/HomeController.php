<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $resume = config('resume');

        $hasAvatar = file_exists(public_path(ltrim($resume['avatar'] ?? '', '/')));

        $initials = collect(explode(' ', $resume['name']))
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        return view('home', compact('resume', 'hasAvatar', 'initials'));
    }
}