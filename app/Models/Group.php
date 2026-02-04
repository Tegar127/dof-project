<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'is_private'];

    /**
     * Get the users in this group.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'group_name', 'name');
    }

    /**
     * Get the users who are members of this group (via pivot).
     */
    public function members()
    {
        return $this->belongsToMany(User::class);
    }
}
