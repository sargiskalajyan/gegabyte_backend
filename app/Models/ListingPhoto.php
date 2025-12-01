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
    protected $fillable = ['listing_id', 'url'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
