<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSession{
    public function handle(Request $request, Closure $next){
        if(!session('user_security')){
            return redirect()->route('web.signin')->with('error', 'You need to logged in first!');
        }

        return $next($request);
    }
}
