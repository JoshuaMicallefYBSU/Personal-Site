<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\VATPAC\ATCSessions;

class TestController extends Controller
{
    public function Job()
    {
        // Dispatch the job
        $job = ATCSessions::dispatch();

        // Call the handle method directly to get the result synchronously
        $result = $job->handle();

        return response()->json([
            'message' => 'Job executed successfully',
            'data' => $result,
        ]);
    }
}
