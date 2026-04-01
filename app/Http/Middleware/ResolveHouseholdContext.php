<?php

namespace App\Http\Middleware;

use App\Models\HouseholdMembership;
use App\Support\Tenancy\CurrentHousehold;
use Closure;
use Illuminate\Http\Request;

class ResolveHouseholdContext
{
    public function handle(Request $request, Closure $next)
    {
        $context = app(CurrentHousehold::class);
        $context->set(null);

        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $headerHouseholdId = $request->header('X-Household-Id');
        $sessionHouseholdId = $request->hasSession()
            ? $request->session()->get('active_household_id')
            : null;
        $householdId = $headerHouseholdId ?: $sessionHouseholdId;

        if (!$householdId) {
            return $next($request);
        }

        $householdId = (int) $householdId;

        $isApprovedMember = HouseholdMembership::query()
            ->where('household_id', $householdId)
            ->where('user_id', $user->id)
            ->where('status', HouseholdMembership::STATUS_APPROVED)
            ->exists();

        abort_unless($isApprovedMember, 403, 'Invalid household context.');

        $context->set($householdId);

        if ($request->hasSession() && $request->session()->get('active_household_id') !== $householdId) {
            $request->session()->put('active_household_id', $householdId);
        }

        return $next($request);
    }
}
