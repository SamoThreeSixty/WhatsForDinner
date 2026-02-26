<?php

namespace App\Http\Controllers;

use App\Mail\HouseholdAccessInviteMail;
use App\Models\Household;
use App\Models\HouseholdAccess;
use App\Models\HouseholdMembership;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
            'slug' => (string) Str::uuid(),
            'locale' => $validated['locale'] ?? 'en',
            'currency' => strtoupper($validated['currency'] ?? 'GBP'),
            'new_members' => true,
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
            'slug' => ['required', 'string', 'max:64'],
        ]);

        $household = Household::where('slug', $validated['slug'])->firstOrFail();

        if (!$household->new_members) {
            return response()->json([
                'message' => 'This household is not accepting new members.',
            ], 422);
        }

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

    public function management(Request $request, Household $household): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $household->id);

        $members = HouseholdMembership::query()
            ->where('household_id', $household->id)
            ->where('status', HouseholdMembership::STATUS_APPROVED)
            ->with('user:id,name,email')
            ->orderByDesc('approved_at')
            ->get();

        $pending = HouseholdMembership::query()
            ->where('household_id', $household->id)
            ->where('status', HouseholdMembership::STATUS_PENDING)
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        $accesses = HouseholdAccess::query()
            ->where('household_id', $household->id)
            ->where('status', HouseholdAccess::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'status', 'expires_at', 'created_at']);

        return response()->json([
            'household' => $household,
            'members' => $members,
            'pending' => $pending,
            'accesses' => $accesses,
        ]);
    }

    public function sendAccess(Request $request, Household $household): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $household->id);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower($validated['email']);

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $alreadyMember = HouseholdMembership::query()
                ->where('household_id', $household->id)
                ->where('user_id', $existingUser->id)
                ->where('status', HouseholdMembership::STATUS_APPROVED)
                ->exists();

            if ($alreadyMember) {
                return response()->json(['message' => 'User is already a household member.'], 422);
            }
        }

        $access = HouseholdAccess::create([
            'household_id' => $household->id,
            'invited_by' => $request->user()->id,
            'name' => $validated['name'] ?? null,
            'email' => $email,
            'token' => Str::random(64),
            'status' => HouseholdAccess::STATUS_PENDING,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        Mail::to($email)->send(new HouseholdAccessInviteMail(
            household: $household,
            token: $access->token,
            hasAccount: (bool) $existingUser,
            name: $validated['name'] ?? null,
        ));

        return response()->json([
            'message' => 'Access email sent.',
        ], 201);
    }

    public function redeemInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:20'],
        ]);

        $access = HouseholdAccess::query()
            ->where('token', $validated['token'])
            ->where('status', HouseholdAccess::STATUS_PENDING)
            ->firstOrFail();

        if ($access->expires_at && $access->expires_at->isPast()) {
            return response()->json([
                'message' => 'Invite has expired.',
            ], 422);
        }

        abort_unless(strtolower($request->user()->email) === strtolower($access->email), 403, 'Invite email does not match this account.');

        $membership = HouseholdMembership::firstOrNew([
            'household_id' => $access->household_id,
            'user_id' => $request->user()->id,
        ]);

        $membership->role = HouseholdMembership::ROLE_MEMBER;
        $membership->status = HouseholdMembership::STATUS_APPROVED;
        $membership->approved_at = Carbon::now();
        $membership->approved_by = $access->invited_by;
        $membership->save();

        $access->status = HouseholdAccess::STATUS_ACCEPTED;
        $access->accepted_at = Carbon::now();
        $access->save();

        $request->session()->put('active_household_id', $access->household_id);

        return response()->json([
            'message' => 'Invite accepted.',
            'active_household_id' => $access->household_id,
        ]);
    }

    public function updateOpenMembership(Request $request, Household $household): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $household->id);

        $validated = $request->validate([
            'new_members' => ['required', 'boolean'],
        ]);

        $household->new_members = (bool) $validated['new_members'];
        $household->save();

        return response()->json([
            'message' => 'Household member access updated.',
            'household' => $household,
        ]);
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

    public function removeMember(Request $request, HouseholdMembership $membership): JsonResponse
    {
        $this->assertCanManageHousehold($request->user()->id, $membership->household_id);

        if ($membership->role === HouseholdMembership::ROLE_OWNER) {
            return response()->json([
                'message' => 'Owner cannot be removed.',
            ], 422);
        }

        $membership->delete();

        return response()->json([
            'message' => 'Member removed.',
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
}
