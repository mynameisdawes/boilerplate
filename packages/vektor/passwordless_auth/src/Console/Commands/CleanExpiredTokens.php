<?php

namespace Vektor\PasswordlessAuth\Console\Commands;

use Illuminate\Console\Command;
use Vektor\PasswordlessAuth\Models\AuthToken;

class CleanExpiredTokens extends Command
{
    protected $signature = 'passwordless:clean-tokens';
    protected $description = 'Remove expired passwordless authentication tokens';

    public function handle()
    {
        $deleted = AuthToken::where('expires_at', '<', now())->delete();

        $this->info("Cleaned {$deleted} expired tokens.");

        return 0;
    }
}
