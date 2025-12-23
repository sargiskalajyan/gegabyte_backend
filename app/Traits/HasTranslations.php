<?php

namespace App\Traits;

trait HasTranslations
{

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

        return null;
    }
}
