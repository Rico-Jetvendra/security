<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Application;

class VerifyLoggerApiKey{
    public function handle(Request $request, Closure $next){
        $requestKey     = $request->header('X-API-KEY');
        $applications   = Application::all();
        $valid          = false;

        if (!$requestKey) {
            return response()->json([
                'message' => 'API Key Missing'
            ], 401);
        }

        foreach ($applications as $app) {
            if (Hash::check($requestKey, $app->application_key)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
