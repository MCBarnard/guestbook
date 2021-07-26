<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if(auth()->user()->is_admin == 1){
                return $next($request);
            }
        } catch (\Throwable $exception){
            return redirect('/login')->with('error',"You don't have the right permissions.");
        }

        return redirect('/login')->with('error',"You don't have the right permissions.");
    }
}
