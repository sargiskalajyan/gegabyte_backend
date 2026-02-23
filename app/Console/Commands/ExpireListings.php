<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireListings extends Command
{
    private const CHUNK_SIZE = 500;

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

        $now = now();
        $topExpirations = [];
        $processedPublished = false;
        $processedTop = false;

        Listing::with(['user.language'])
            ->where('status', '=','published')
            ->whereNotNull('published_until')
            ->where('published_until', '<', $now)
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($listings) use (&$topExpirations, &$processedPublished) {
                $processedPublished = true;
                foreach ($listings as $listing) {
                    if ($listing->is_top) {
                        $this->incrementTopUsage($topExpirations, $listing->user_id);
                        $listing->is_top = false;
                        $listing->top_expires_at = null;
                    }

                    $listing->status = 'expired';
                    $listing->save();

                    $this->sendExpiredSms($listing);
                }
            });

        Listing::with('user.language')
            ->where('is_top', '=',true)
            ->whereNotNull('top_expires_at')
            ->where('top_expires_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($listings) use (&$topExpirations, &$processedTop) {
                $processedTop = true;
                foreach ($listings as $listing) {
                    $this->incrementTopUsage($topExpirations, $listing->user_id);
                    $listing->is_top = false;
                    $listing->top_expires_at = null;
                    $listing->save();
                }
            });

        if (!$processedPublished && !$processedTop) {
            return Command::SUCCESS;
        }

        if (!empty($topExpirations)) {
            DB::beginTransaction();
            try {
                foreach ($topExpirations as $userId => $count) {
                    $userPackage = User::find($userId)?->activePackage();
                    if ($userPackage && $userPackage->exists && ($userPackage->used_top_listings ?? 0) > 0) {
                        $decrement = min($userPackage->used_top_listings, $count);
                        if ($decrement > 0) {
                            $userPackage->decrement('used_top_listings', $decrement);
                        }
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return Command::SUCCESS;

    }


    /**
     * @param array $topExpirations
     * @param int $userId
     * @return void
     */
    private function incrementTopUsage(array &$topExpirations, int $userId): void
    {
        $topExpirations[$userId] = ($topExpirations[$userId] ?? 0) + 1;
    }


    /**
     * @param Listing $listing
     * @return void
     */
    private function sendExpiredSms(Listing $listing): void
    {
        $user = $listing->user;

        if (!$user || !$user->phone_number || !$user->phone_number_verified_at) {
            return;
        }

        $locale = $user->language?->code ?? app()->getLocale() ?? 'hy';
        $message = trans('listings.sms_expired', [], $locale);

        app(SmsService::class)->sendSms($user->phone_number, $message);
    }
}
