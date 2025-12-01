<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * @var string
     */
    protected $table = 'locations';

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Parent location (nullable)
     */
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Child locations
     */
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Translations relationship
     */
    public function translations()
    {
        return $this->hasMany(LocationTranslation::class);
    }


    /**
     * @param $locale
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->hasOne(LocationTranslation::class)
            ->whereHas('language', function ($q) use ($locale) {
                $q->where('code', $locale);
            });
    }


    /**
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        // If relation 'translation' is eager loaded it will be used.
        if ($this->relationLoaded('translation') && $this->translation) {
            return $this->translation->name;
        }

        // Try to find translation by current locale among loaded translations or via query
        $locale = app()->getLocale();

        // Prefer loaded translations collection if available
        if ($this->relationLoaded('translations')) {
            $match = $this->translations->first(function ($t) use ($locale) {
                return $t->language?->code === $locale;
            });

            if ($match) {
                return $match->name;
            }

            // fallback to english
            $en = $this->translations->first(function ($t) {
                return $t->language?->code === 'en';
            });

            return $en?->name ?? $this->translations->first()?->name;
        }

        // As a last resort, query the DB for the translation
        $translation = $this->translation($locale)->first();
        if ($translation) {
            return $translation->name;
        }

        // fallback to english translation
        $english = $this->hasOne(FuelTranslation::class)
            ->whereHas('language', function ($q) {
                $q->where('code', 'en');
            })->first();

        return $english?->name;
    }
}
