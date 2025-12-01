<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTranslation extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'package_translations';

    protected $fillable = [
        'price',
        'duration_days',
        'max_active_listings',
        'included_featured_days',
        'is_active'
    ];

    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
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
