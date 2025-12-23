<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasEquipment extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @var string
     */
    protected $table = 'gas_equipments';

    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(GasEquipmentTranslation::class);
    }


    /**
     * @param $locale
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->hasOne(GasEquipmentTranslation::class)
            ->whereHas('language', fn ($q) => $q->where('code', $locale));
    }


    /**
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        if ($this->relationLoaded('translation') && $this->translation) {
            return $this->translation->name;
        }

        $locale = app()->getLocale();

        if ($this->relationLoaded('translations')) {
            $match = $this->translations->first(
                fn ($t) => $t->language?->code === $locale
            );

            if ($match) return $match->name;

            $en = $this->translations->first(
                fn ($t) => $t->language?->code === 'en'
            );

            return $en?->name ?? $this->translations->first()?->name;
        }

        $translation = $this->translation($locale)->first();
        if ($translation) return $translation->name;

        $english = $this->hasOne(GasEquipmentTranslation::class)
            ->whereHas('language', fn ($q) => $q->where('code', 'en'))
            ->first();

        return $english?->name;
    }
}
