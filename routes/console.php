<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\VATPAC\ATCSessions;
use App\Jobs\RCL\OperationsCenter;

// Every Minute
// Schedule::job(new ATCSessions)->everyMinute();
Schedule::job(new OperationsCenter)->everyMinute();
