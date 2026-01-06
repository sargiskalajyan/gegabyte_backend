<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingPhoto extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = ['listing_id', 'url',  'is_default', 'thumbnail'];


    /**
     * @param $value
     * @return string
     */
    public function getUrlAttribute($value)
    {
        // if the raw DB value already looks like a URL, return it
        if (preg_match('/^https?:\/\//', $value)) {
            return $value;
        }

        return asset('storage/' . $value);
    }


    /**
     * @param $value
     * @return string|null
     */
    public function getThumbnailAttribute($value)
    {
        if (! $value) return null;
        if (preg_match('/^https?:\/\//', $value)) return $value;
        return asset('storage/' . $value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
