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

    /**
     * @var bool
     */
    public $timestamps = true;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(PackageTranslation::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasOne
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
