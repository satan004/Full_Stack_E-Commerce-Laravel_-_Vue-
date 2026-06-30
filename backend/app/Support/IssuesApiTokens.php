<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

trait IssuesApiTokens
{
    protected function issueToken(User $user): string
    {
        $plainToken = Str::random(48);

        $user->apiTokens()->create([
            'name' => 'customer',
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
        ]);

        return $plainToken;
    }
}
