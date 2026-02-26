<?php

namespace App\Http\Controllers;

use App\Models\HouseholdAccess;
use App\Models\HouseholdMembership;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'invite_token' => ['nullable', 'string', 'min:20'],
        ]);

        $email = strtolower($validated['email']);
        $providedInviteToken = trim((string) ($validated['invite_token'] ?? ''));

        $pendingInviteForEmail = HouseholdAccess::query()
            ->where('email', $email)
            ->where('status', HouseholdAccess::STATUS_PENDING)
            ->latest('id')
            ->first();

        $inviteToRedeem = null;

        if ($pendingInviteForEmail) {
            if ($providedInviteToken === '') {
                throw ValidationException::withMessages([
                    'invite_token' => ['An invite token is required to register this invited email address.'],
                ]);
            }

            $inviteToRedeem = HouseholdAccess::query()
                ->where('token', $providedInviteToken)
                ->where('status', HouseholdAccess::STATUS_PENDING)
                ->first();

            if (!$inviteToRedeem || strtolower($inviteToRedeem->email) !== $email) {
                throw ValidationException::withMessages([
                    'invite_token' => ['The invite token is invalid for this email address.'],
                ]);
            }

            if ($inviteToRedeem->expires_at && $inviteToRedeem->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'invite_token' => ['The invite token has expired.'],
                ]);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => Hash::make($validated['password']),
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($inviteToRedeem) {
            $membership = HouseholdMembership::firstOrNew([
                'household_id' => $inviteToRedeem->household_id,
                'user_id' => $user->id,
            ]);

            $membership->role = HouseholdMembership::ROLE_MEMBER;
            $membership->status = HouseholdMembership::STATUS_APPROVED;
            $membership->approved_at = Carbon::now();
            $membership->approved_by = $inviteToRedeem->invited_by;
            $membership->save();

            $inviteToRedeem->status = HouseholdAccess::STATUS_ACCEPTED;
            $inviteToRedeem->accepted_at = Carbon::now();
            $inviteToRedeem->save();

            $request->session()->put('active_household_id', $inviteToRedeem->household_id);
        }

        return response()->json([
            'message' => 'Account created successfully. Please verify your email.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $request->user(),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ]);

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'If an unverified account exists for this email, a verification link has been sent.',
        ]);
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/login?verified=1');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function verify(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'authenticated' => true,
            'verified' => $user->hasVerifiedEmail(),
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
