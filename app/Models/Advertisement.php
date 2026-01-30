<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'price',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(AdvertisementTranslation::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function translation()
    {
        $locale = app()->getLocale();

        return $this->hasOne(AdvertisementTranslation::class)
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('code', $locale);
            });
    }


    /**
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed|string
     */
    public function getNameAttribute()
    {
        $translation = $this->translation()->first();

        if (!$translation) {
            $translation = $this->translations()
                ->whereHas('language', fn($q) => $q->where('code', 'en'))
                ->first();
        }

        return $translation?->name ?? '';
    }

}
