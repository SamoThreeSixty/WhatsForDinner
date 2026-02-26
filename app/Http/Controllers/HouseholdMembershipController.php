<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdMembership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HouseholdMembershipController extends Controller
{
    public function myHouseholds(Request $request): JsonResponse
    {
        $households = $request->user()
            ->households()
            ->wherePivot('status', HouseholdMembership::STATUS_APPROVED)
            ->get();

        return response()->json([
            'active_household_id' => $request->session()->get('active_household_id'),
            'households' => $households,
        ]);
    }

    public function createHousehold(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'max:3'],
        ]);

        $household = Household::create([
            'name' => $validated['name'],
            'slug' => $this->makeUniqueSlug($validated['name']),
            'locale' => $validated['locale'] ?? 'en',
            'currency' => strtoupper($validated['currency'] ?? 'GBP'),
        ]);

        HouseholdMembership::create([
            'household_id' => $household->id,
            'user_id' => $request->user()->id,
            'role' => HouseholdMembership::ROLE_OWNER,
            'status' => HouseholdMembership::STATUS_APPROVED,
            'approved_at' => Carbon::now(),
            'approved_by' => $request->user()->id,
        ]);

        $request->session()->put('active_household_id', $household->id);

        return response()->json([
            'message' => 'Household created.',
            'household' => $household,
        ], 201);
    }

    public function setActiveHousehold(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'household_id' => ['required', 'integer', 'exists:households,id'],
        ]);

        $isApprovedMember = HouseholdMembership::query()
            ->where('household_id', $validated['household_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', HouseholdMembership::STATUS_APPROVED)
            ->exists();

        abort_unless($isApprovedMember, 403, 'You are not a member of that household.');

        $request->session()->put('active_household_id', (int) $validated['household_id']);

        return response()->json([
            'message' => 'Active household set.',
            'active_household_id' => (int) $validated['household_id'],
        ]);
    }

    public function requestJoin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'join_code' => ['required', 'string', 'max:16'],
        ]);

        $household = Household::where('join_code', strtoupper($validated['join_code']))->firstOrFail();

        $membership = HouseholdMembership::firstOrNew([
            'household_id' => $household->id,
            'user_id' => $request->user()->id,
        ]);

        if ($membership->exists && $membership->status === HouseholdMembership::STATUS_APPROVED) {
            return response()->json([
                'message' => 'You are already in this household.',
            ], 200);
        }

        $membership->role = HouseholdMembership::ROLE_MEMBER;
        $membership->status = HouseholdMembership::STATUS_PENDING;
        $membership->approved_at = null;
        $membership->approved_by = null;
        $membership->save();

        return response()->json([
            'message' => 'Join request submitted and awaiting approval.',
        ], 202);
    }

    public function pendingRequests(Request $request, Household $household): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $household->id);

        $requests = HouseholdMembership::query()
            ->where('household_id', $household->id)
            ->where('status', HouseholdMembership::STATUS_PENDING)
            ->with('user:id,name,email')
            ->get();

        return response()->json($requests);
    }

    public function approve(Request $request, HouseholdMembership $membership): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $membership->household_id);

        if ($membership->status !== HouseholdMembership::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending requests can be approved.',
            ], 422);
        }

        $membership->status = HouseholdMembership::STATUS_APPROVED;
        $membership->approved_at = Carbon::now();
        $membership->approved_by = $request->user()->id;
        $membership->save();

        return response()->json([
            'message' => 'Membership approved.',
            'membership' => $membership,
        ]);
    }

    public function reject(Request $request, HouseholdMembership $membership): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $membership->household_id);

        if ($membership->status !== HouseholdMembership::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending requests can be rejected.',
            ], 422);
        }

        $membership->status = HouseholdMembership::STATUS_REJECTED;
        $membership->approved_at = null;
        $membership->approved_by = $request->user()->id;
        $membership->save();

        return response()->json([
            'message' => 'Membership rejected.',
            'membership' => $membership,
        ]);
    }

    private function assertCanManageHousehold(int $userId, int $householdId): void
    {
        $isManager = HouseholdMembership::query()
            ->where('household_id', $householdId)
            ->where('user_id', $userId)
            ->where('status', HouseholdMembership::STATUS_APPROVED)
            ->whereIn('role', [HouseholdMembership::ROLE_OWNER, HouseholdMembership::ROLE_ADMIN])
            ->exists();

        abort_unless($isManager, 403, 'Not authorized to manage this household.');
    }

    private function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'household';

        $slug = $base;
        $suffix = 1;

        while (Household::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function makeUniqueJoinCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Household::where('join_code', $code)->exists());

        return $code;
    }
}
