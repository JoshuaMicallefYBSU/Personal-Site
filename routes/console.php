<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\VATPAC\ATCSessions;

// Every Minute
Schedule::job(new ATCSessions)->everyMinute();
