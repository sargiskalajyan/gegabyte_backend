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
    public function activePackage(): UserPackage
    {
        // Check if active package exists
        $record = $this->userPackages()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at')
            ->first();

        // Create FREE package if user has no active package
        if (!$record) {
            $freePackage = Package::where('price', 0)->firstOrFail();

            $record = $this->userPackages()->create([
                'package_id'  => $freePackage->id,
                'starts_at'   => now(),
                'expires_at'  => null,
                'status'      => 'active',
            ]);
        }

        return $record;
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
