<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirePackages extends Command
{
    protected $signature = 'packages:expire';
    protected $description = 'Expire user packages and update listings accordingly';

    public function handle()
    {
        $now = Carbon::now();

        $expiring = UserPackage::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expiring as $up) {
            try {
                $up->expire();

                // Optionally expire all user's active listings
                \App\Models\Listing::where('user_id', $up->user_id)
                    ->whereIn('status', ['published','pending'])
                    ->update(['status' => 'expired']);

                // Notify user
                // $up->user->notify(new PackageExpiredNotification($up));

            } catch (\Throwable $e) {
                Log::error('ExpirePackages error', ['id' => $up->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info('Expired packages processed: ' . $expiring->count());
    }
}
