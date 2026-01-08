<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Carbon;

class TopListingService
{
    protected function getActivePackage($user)
    {
        return $user->activePackage ?? $user->package ?? null;
    }

    protected function getActiveUserPackageRecord($user)
    {
        // purchased package record that contains used_active_listings
        return $user->activePackage ?? $user->packageRecord ?? null;
    }

    protected function currentTopCount($user): int
    {
        if (method_exists($user, 'listings')) {
            return $user->listings()->topActive()->count();
        }
        return 0;
    }

    public function canAssignTop($user): bool
    {
        $package = $this->getActivePackage($user);
        if (!$package) {
            return false;
        }

        $purchased = $this->getActiveUserPackageRecord($user);
        if ($purchased && isset($purchased->used_active_listings)) {
            return $purchased->used_active_listings > 0;
        }

        return $package->top_active_listings_count > 0
            && $this->currentTopCount($user) < $package->top_active_listings_count;
    }

    protected function adjustRemainingTopSlots($user, int $delta): void
    {
        $purchased = $this->getActiveUserPackageRecord($user);
        if (!$purchased || !isset($purchased->used_active_listings)) {
            return;
        }

        $purchased->used_active_listings = max(0, $purchased->used_active_listings + $delta);
        $purchased->save();
    }

    /**
     * Assign top status to a listing. $days nullable => indefinite.
     *
     * @throws \Exception on validation/limit errors
     */
    public function assignTop(Listing $listing, $user, ?int $days = null): Listing
    {
        if ($listing->user_id !== $user->id) {
            throw new \Exception('You do not own this listing.');
        }

        if (!$this->canAssignTop($user)) {
            throw new \Exception('Top listing limit reached or not available in your package.');
        }

        $listing->is_top = true;
        $listing->top_expires_at = $days ? Carbon::now()->addDays($days) : null;
        $listing->save();

        // decrement remaining slots by 1
        $this->adjustRemainingTopSlots($user, -1);

        return $listing;
    }

    public function revokeTop(Listing $listing, $user): Listing
    {
        if ($listing->user_id !== $user->id) {
            throw new \Exception('You do not own this listing.');
        }

        $listing->is_top = false;
        $listing->top_expires_at = null;
        $listing->save();

        // return one slot to the user's purchased package
        $this->adjustRemainingTopSlots($user, +1);

        return $listing;
    }
}
