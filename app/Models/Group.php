<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the users in this group.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'group_name', 'name');
    }
}
