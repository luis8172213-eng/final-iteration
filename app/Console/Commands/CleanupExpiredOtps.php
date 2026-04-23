<?php

namespace App\Console\Commands;

use App\Models\Otp;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    protected $signature = 'otps:cleanup';

    protected $description = 'Delete expired OTP records from database';

    public function handle()
    {
        $deleted = Otp::where('expires_at', '<', now())->delete();

        $this->info("Deleted {$deleted} expired OTP records.");

        return 0;
    }
}
