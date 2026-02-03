<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username','email','password','language_id','phone_number','profile_image','location_id'
    ];


    /**
     * @var string[]
     */
    protected $appends = ['location_name'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_number_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    /**
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }


    /**
     * @return string|null
     */
    public function getLocationNameAttribute(): ?string
    {

        if (!empty($this->attributes['location_name'])) {
            return $this->attributes['location_name'];
        }
        return $this->location?->name;
    }


    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed
     */
    public function activePackage2(): UserPackage
    {
        // Auto-expire old packages (NO CRON NEEDED)
        $this->userPackages()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        // Now get the active package after clearing expired ones
        $record = $this->userPackages()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at')
            ->first();

        // Create FREE package if none exists
        if (!$record) {
            $freePackage = Package::where('price', 0)->firstOrFail();

            $record = $this->userPackages()->create([
                'package_id' => $freePackage->id,
                'starts_at'  => now(),
                'expires_at' => null,
                'status'     => 'active',
            ]);
        }

        return $record;
    }


    /**
     * @return UserPackage
     */
    public function activePackage3(): UserPackage
    {
        /**
         * 1️⃣ Expire outdated packages
         */
        $this->userPackages()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        /**
         * 2️⃣ Get latest valid active package
         */
        $record = $this->userPackages()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at')
            ->first();

        /**
         * 3️⃣ If multiple active packages exist → expire older ones
         */
        if ($record) {
            $this->userPackages()
                ->where('status', 'active')
                ->where('id', '!=', $record->id)
                ->update(['status' => 'expired']);

            return $record;
        }

        /**
         * 4️⃣ Create FREE package safely (idempotent)
         */
        $freePackage = Package::where('price', 0)->firstOrFail();

        return $this->userPackages()->firstOrCreate(
            [
                'package_id' => $freePackage->id,
                'status'     => 'active',
            ],
            [
                'starts_at'  => now(),
                'expires_at'=> null,
            ]
        );
    }


    /**
     * @return UserPackage
     */
    public function activePackage(): UserPackage
    {
        // 1️⃣ Expire outdated paid packages
        $this->userPackages()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        // 2️⃣ Get active paid package
        $record = $this->userPackages()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at')
            ->first();

        if ($record) {
            return $record;
        }

        // 3️⃣ RETURN VIRTUAL FREE PACKAGE (NOT SAVED)
        $free = Package::where('price', 0)->firstOrFail();

        return new UserPackage([
            'package_id' => $free->id,
            'status'     => 'active',
            'starts_at'  => now(),
            'expires_at'=> null,
            'package'    => $free,
        ]);
    }


    /**
     * @return bool
     */
    public function canActivateListing(): bool
    {
        $package = $this->activePackage();
        $max = $package->package->max_active_listings ?? 0;

        $activeCount = $this->listings()
            ->whereIn('status', ['pending', 'published'])
            ->count();

        return $activeCount < $max;
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(UserPackage::class);
    }

}
