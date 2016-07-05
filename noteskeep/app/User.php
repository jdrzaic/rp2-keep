<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class representing user of an app
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function note() {
        return $this->belongsToMany(Note::class);
    }
}
