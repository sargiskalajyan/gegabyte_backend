<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverTypeTranslation extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'driver_type_translations';

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
    public function driverType()
    {
        return $this->belongsTo(DriverType::class);
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
