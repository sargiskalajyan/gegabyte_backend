<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'languages';

    /**
     * @var string[]
     */
    protected $fillable = ['code', 'name'];


    /**
     * @var bool
     */
    public $timestamps = false;
}
