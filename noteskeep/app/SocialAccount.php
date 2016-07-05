<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class representing social account(user)
 * Class SocialAccount
 * @package App
 */
class SocialAccount extends Model {

    /**
     * id - user id
     * provider_user_id - social account user id
     * provider - facebook/google
     * @var array
     */
    protected $fillable = ['user_id', 'provider_user_id', 'provider'];

    /**
     * Many to one relationship to user(Google, Facebook)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
