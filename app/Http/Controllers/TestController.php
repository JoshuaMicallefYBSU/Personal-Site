<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\VATPAC\ATCSessions;
use App\Jobs\RCL\OperationsCenter;
use App\Jobs\RCL\ATISUpdates;

class TestController extends Controller
{
    public function Job()
    {
        // Dispatch the job
        $job = ATISUpdates::dispatch();

        // Call the handle method directly to get the result synchronously
        $result = $job->handle();

        return response()->json([
            'message' => 'Job executed successfully',
            'data' => $result,
        ]);
    }
}
