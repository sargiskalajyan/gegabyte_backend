<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationTranslation extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'location_translations';

    /**
     * @var string[]
     */
    protected $guarded = ['id'];


    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }


    /**
     * @return string|null
     */
    public function getLocaleAttribute(): ?string
    {
        return $this->language?->code;
    }
}
