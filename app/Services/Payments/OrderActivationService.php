<?php

namespace App\Services\Payments;

use App\Models\Listing;
use App\Models\Order;
use App\Models\UserPackage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderActivationService
{
    /**
     * Mark an order as paid and activate the related package or advertisement.
     *
     * @param Order $order
     * @param array $payload
     * @param string|null $reference
     * @return Order
     */
    public function activate(Order $order, array $payload = [], ?string $reference = null): Order
    {
        return DB::transaction(function () use ($order, $payload, $reference) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $mergedPayload = array_merge($order->payload ?? [], $payload);

            if (! $order->advertisement_id && isset($mergedPayload['advertisement_id'])) {
                $order->advertisement_id = $mergedPayload['advertisement_id'];
                $order->save();
            }

            $order->markPaid($reference ?? $order->reference, $mergedPayload);
            $order->loadMissing('package', 'advertisement', 'user');

            if ($order->package) {
                $this->activatePackage($order);
            }

            if ($order->advertisement) {
                $this->activateAdvertisement($order, $mergedPayload);
            }

            return $order->refresh()->load('package', 'advertisement', 'user');
        });
    }

    /**
     * @param Order $order
     * @return void
     */
    protected function activatePackage(Order $order): void
    {
        $user = $order->user;
        $package = $order->package;

        $user->packages()->where('status', 'active')->update(['status' => 'expired']);

        UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'starts_at' => now(),
            'expires_at' => $package->duration_days
                ? now()->addDays($package->duration_days)
                : null,
            'used_active_listings' => 0,
            'used_featured_days' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * @param Order $order
     * @param array $payload
     * @return void
     */
    protected function activateAdvertisement(Order $order, array $payload): void
    {
        $listingId = $payload['listing_id'] ?? ($order->payload['listing_id'] ?? null);

        if (! $listingId) {
            Log::warning('Advertisement order missing listing reference', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $listing = Listing::find($listingId);

        if (! $listing) {
            Log::warning('Listing not found for advertisement order', [
                'order_id' => $order->id,
                'listing_id' => $listingId,
            ]);

            return;
        }

        $expires = $order->advertisement->duration_days
            ? now()->addDays($order->advertisement->duration_days)
            : null;

        $this->applyTopToListing($listing, $expires);
    }

    /**
     * Mark listing as TOP and ensure its published_until is at least the TOP duration.
     * If listing already expires later than TOP, do not reduce its expiration.
     *
     * @param Listing $listing
     * @param Carbon|null $expires
     * @return void
     */
    protected function applyTopToListing(Listing $listing, ?Carbon $expires): void
    {
        $listing->is_top = true;
        $listing->top_expires_at = $expires;

        if ($expires) {
            $minPublishedUntil = $expires->copy();

            $currentPublishedUntil = $listing->published_until
                ? Carbon::parse($listing->published_until)
                : null;

            if (is_null($currentPublishedUntil) || $currentPublishedUntil->lt($minPublishedUntil)) {
                $listing->published_until = $minPublishedUntil;
            }
        }

        $listing->save();
    }
}