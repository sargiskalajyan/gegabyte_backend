<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmissionTranslation extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'transmission_translations';


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
    public function transmission()
    {
        return $this->belongsTo(Transmission::class);
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
