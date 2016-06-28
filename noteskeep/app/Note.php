<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['content'];

    public function tag() {
        return $this->belongsToMany(Tag::class);
    }

    public function user() {
        return $this->belongsToMany(User::class);
    }
}
