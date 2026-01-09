<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire published listings at midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        Listing::where('status', 'published')
//            ->whereNotNull('published_until')
//            ->where('published_until', '<=', now()->startOfDay())
//            ->update([
//                'status' => 'expired',
//            ]);
//
//        return Command::SUCCESS;


        // Expire published listings (published_until)
        $toExpire = Listing::where('status', 'published')
            ->whereNotNull('published_until')
            ->where('published_until', '<', now())
            ->get();

        // Expire top status based on top_expires_at (independent of published status)
        $topToExpire = Listing::where('is_top', true)
            ->whereNotNull('top_expires_at')
            ->where('top_expires_at', '<=', now())
            ->get();

        if ($toExpire->isEmpty() && $topToExpire->isEmpty()) {
            return Command::SUCCESS;
        }

        // Handle top listings counters per user (from both expiration sources)
        $topByUser = $toExpire->where('is_top', true)->merge($topToExpire)->groupBy('user_id');

        DB::beginTransaction();
        try {
            // For each user, decrement used_top_listings by number of top listings expiring
            foreach ($topByUser as $userId => $group) {
                $count = $group->count();

                $userPackage = \App\Models\User::find($userId)?->activePackage();
                if ($userPackage && $userPackage->exists && ($userPackage->used_top_listings ?? 0) > 0) {
                    $decrement = min($userPackage->used_top_listings, $count);
                    $userPackage->decrement('used_top_listings', $decrement);
                }
            }

            // Mark listings expired and clear is_top flag
            foreach ($toExpire as $listing) {
                $listing->status = 'expired';
                if ($listing->is_top) {
                    $listing->is_top = false;
                    $listing->top_expires_at = null;
                }
                $listing->save();
            }

            // Clear top flag for listings whose top period ended
            foreach ($topToExpire as $listing) {
                if ($listing->is_top) {
                    $listing->is_top = false;
                }
                $listing->top_expires_at = null;
                $listing->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return Command::SUCCESS;

    }
}
