<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class represents note tag(one word)
 * Class Tag
 * @package App
 */
class Tag extends Model
{
    /**
     * name-actual tag
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Many to many relation to Note
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function note() {
        return $this->belongsToMany(Note::class);
    }
}
