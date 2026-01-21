<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'status',
        'author_id',
        'author_name',
        'content_data',
        'history_log',
        'feedback',
        'target_role',
        'target_value',
    ];

    protected function casts(): array
    {
        return [
            'content_data' => 'array',
            'history_log' => 'array',
        ];
    }

    /**
     * Get the author of the document.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope to get documents by role.
     */
    public function scopeForUser($query, $user)
    {
        if ($user->role === 'reviewer') {
            return $query->where('target_role', 'dispo')
                         ->whereIn('status', ['pending_review', 'approved']);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('author_id', $user->id)
              ->orWhere(function ($sq) use ($user) {
                  $sq->where('target_role', 'group')
                     ->where('target_value', $user->group_name)
                     ->whereIn('status', ['sent', 'received']);
              });
        });
    }
}
