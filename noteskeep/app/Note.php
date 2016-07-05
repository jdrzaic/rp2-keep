<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class represent a note.
 * Class Note
 * @package App
 */
class Note extends Model
{
    /*
     * Content-content of a note
     * Owner-creator of a note
     */
    protected $fillable = ['content', 'owner'];

    /**
     * Many to many relation between Note and Tag object
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tag() {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Many to many relation between Note and User
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user() {
        return $this->belongsToMany(User::class);
    }
}
