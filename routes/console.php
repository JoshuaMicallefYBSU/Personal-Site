<?php

use Illuminate\Support\Facades\Schedule;

// Every Minute
Schedule::job(new ATCSessions)->everyMinute();
