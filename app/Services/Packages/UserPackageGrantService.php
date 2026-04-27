<?php

namespace App\Services\Packages;

use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPackageGrantService
{
    /**
     * Grant a package to a user by expiring the current active package(s)
     * and creating a new active user package record.
     *
     * @param User $user
     * @param Package $package
     * @return UserPackage
     */
    public function grant(User $user, Package $package): UserPackage
    {
        return DB::transaction(function () use ($user, $package) {
            $user = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $package = Package::query()
                ->whereKey($package->id)
                ->where('is_active', true)
                ->firstOrFail();

            $user->userPackages()
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => 0,
                'gateway' => 'other',
                'status' => 'paid',
                'reference' => 'admin-' . Str::uuid(),
                'description' => 'Admin granted package #' . $package->id,
                'idempotency_key' => (string) Str::uuid(),
            ]);

            return $user->userPackages()->create([
                'package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => $package->duration_days
                    ? now()->addDays($package->duration_days)
                    : null,
                'used_active_listings' => 0,
                'used_featured_days' => 0,
                'used_top_listings' => 0,
                'status' => 'active',
            ]);
        });
    }
}