<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdvertisement extends Model
{
    use HasFactory;

    protected $table = 'user_advertisements';

    protected $fillable = [
        'user_id',
        'advertisement_id',
        'listing_id',
        'order_id',
        'price',
        'duration_days',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
