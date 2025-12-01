<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'price',
        'duration_days',
        'max_active_listings',
        'included_featured_days',
        'is_active'
    ];

    // Migration now uses timestamps, so enable them
    public $timestamps = true;

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function translations()
    {
        return $this->hasMany(PackageTranslation::class);
    }

    /**
     * Return translation for the current app locale.
     * Falls back to English if missing.
     */
    public function translation()
    {
        $locale = app()->getLocale();

        return $this->hasOne(PackageTranslation::class)
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('code', $locale);
            });
    }

    /**
     * Convenience accessor: $package->name
     */
    public function getNameAttribute()
    {
        $translation = $this->translation()->first();

        // Fallback to English if missing
        if (!$translation) {
            $translation = $this->translations()
                ->whereHas('language', fn($q) => $q->where('code', 'en'))
                ->first();
        }

        return $translation?->name ?? '';
    }
}
