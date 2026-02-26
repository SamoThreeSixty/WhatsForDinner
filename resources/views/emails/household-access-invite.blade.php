<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Household Invite</title>
</head>
<body style="font-family: Arial, sans-serif; color: #153e2f;">
<p>Hello{{ $inviteeName ? ' ' . $inviteeName : '' }},</p>

<p>You were invited to join the household <strong>{{ $householdName }}</strong>.</p>
<p>Household code (slug): <strong>{{ $householdSlug }}</strong></p>

@if($hasAccount)
<p>You already have an account. Click below to accept and join:</p>
@else
<p>You do not have an account yet. Click below to register, then accept the invite:</p>
@endif

<p><a href="{{ $inviteUrl }}">Accept household invite</a></p>

<p>If you were not expecting this invite, you can ignore this email.</p>
</body>
</html>
