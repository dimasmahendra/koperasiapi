<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\TerminableMiddleware;  
use Illuminate\Support\Facades\Log;

class Logger implements TerminableMiddleware {

    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $log = '';
        $log .= "";
        $log .= $request->ip();
        $log .= " ";
        $log .= $_SERVER['REQUEST_METHOD'];
        $log .= " ";
        $log .= $_SERVER['REQUEST_URI'];
        $log .= "\n\t";

        if($response->headers->get('content-type') == 'application/json')
        {
            $content = $response->getData();
            Log::info($log, ['response' => $response->getData()]);
        }
        // else 
        //    Log::info($log, ['response' => $response]);
    }

}
