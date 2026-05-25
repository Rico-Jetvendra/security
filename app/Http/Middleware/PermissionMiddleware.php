<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware{
    public function handle(Request $request, Closure $next, $permission){
        $permissions = session('permission_security', []);

        if(!in_array($permission, $permissions)){
            return redirect()->route('web.signin')->with('error', 'You don`t have any access for this page!');
        }

        return $next($request);
    }
}
