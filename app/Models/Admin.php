<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class Admin  extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * @var string
     */
    protected $guard = 'admin';


    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];


    /**
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function dashboardSetting()
    {
        return $this->hasOne(DashboardSetting::class);
    }
}
