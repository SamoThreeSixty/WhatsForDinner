<?php

namespace App\Mail;

use App\Models\Household;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HouseholdAccessInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Household $household,
        public string $token,
        public bool $hasAccount,
        public ?string $name,
    ) {
    }

    public function build(): self
    {
        $base = rtrim(config('app.frontend_url', config('app.url')), '/');
        $path = $this->hasAccount ? '/login' : '/register';
        $url = $base . $path . '?invite_token=' . urlencode($this->token);

        return $this
            ->subject('Household invite: ' . $this->household->name)
            ->view('emails.household-access-invite')
            ->with([
                'householdName' => $this->household->name,
                'householdSlug' => $this->household->slug,
                'inviteUrl' => $url,
                'hasAccount' => $this->hasAccount,
                'inviteeName' => $this->name,
            ]);
    }
}
