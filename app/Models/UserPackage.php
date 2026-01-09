<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    use HasFactory;


    /**
     * @var string
     */
    protected $table = 'user_packages';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'starts_at',
        'expires_at',
        'used_active_listings',
        'used_featured_days',
        'used_top_listings',
        'status'
    ];

    /**
     * @var string[]
     */
    protected $dates = ['starts_at','expires_at','created_at','updated_at'];


    /**
     * @return void
     */
    protected static function booted()
    {
        static::retrieved(function (UserPackage $package) {
            if (
                $package->status === 'active' &&
                $package->expires_at &&
                $package->expires_at < now()
            ) {
                $package->update(['status' => 'expired']);
            }
        });
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }


    /**
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }


    /**
     * @return int
     */
    public function remainingListings(): int
    {
        return max(0, $this->package->max_active_listings - $this->used_active_listings);
    }


    /**
     * @return int
     */
    public function remainingTopListings(): int
    {
        return max(0, ($this->package->top_listings_count ?? 0) - ($this->used_top_listings ?? 0));
    }


    /**
     * @return int
     */
    public function remainingFeaturedDays(): int
    {
        return max(0, $this->package->included_featured_days - $this->used_featured_days);
    }


    /**
     * @param Carbon|null $startsAt
     * @return void
     */
    public function activate(Carbon $startsAt = null)
    {
        $startsAt = $startsAt ?: Carbon::now();
        $this->starts_at = $startsAt;
        $this->expires_at = $startsAt->copy()->addDays($this->package->duration_days);
        $this->status = 'active';
        $this->save();
    }


    /**
     * @return void
     */
    public function expire()
    {
        $this->status = 'expired';
        $this->save();
    }
}
