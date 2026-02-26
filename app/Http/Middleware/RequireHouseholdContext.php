<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentHousehold;
use Closure;
use Illuminate\Http\Request;

class RequireHouseholdContext
{
    public function handle(Request $request, Closure $next)
    {
        abort_if(app(CurrentHousehold::class)->id() === null, 422, 'Select a household first.');

        return $next($request);
    }
}
