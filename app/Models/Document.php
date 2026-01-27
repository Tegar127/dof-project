<?php

namespace App\Models;

use App\Enums\DocumentStatus;
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
        'folder_id',
        'version',
        'content_data',
        'history_log',
        'feedback',
        'target_role',
        'target_value',
        'deadline',
        'approval_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'content_data' => 'array',
            'history_log' => 'array',
            'deadline' => 'datetime',
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
                         ->whereIn('status', [DocumentStatus::PENDING_REVIEW, DocumentStatus::APPROVED]);
        }

        return $query->where(function ($q) use ($user) {
            $q->where('author_id', $user->id)
              ->orWhere(function ($sq) use ($user) {
                  $sq->where('target_role', 'group')
                     ->where('target_value', $user->group_name)
                     ->whereIn('status', [DocumentStatus::SENT, DocumentStatus::RECEIVED]);
              });
        });
    }

    /**
     * Get the folder that contains the document.
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get all logs for this document.
     */
    public function logs()
    {
        return $this->hasMany(DocumentLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all read receipts for this document.
     */
    public function readReceipts()
    {
        return $this->hasMany(DocumentReadReceipt::class)->orderBy('read_at', 'desc');
    }

    /**
     * Get all approvals for this document.
     */
    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class)->orderBy('sequence');
    }

    /**
     * Create a log entry for this document.
     */
    public function createLog($action, $user, $notes = null, $statusFrom = null, $statusTo = null)
    {
        return $this->logs()->create([
            'version' => $this->version ?? '1.0',
            'action' => $action,
            'user_id' => $user->id ?? null,
            'user_name' => $user->name ?? 'System',
            'user_position' => $user->position ?? null,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Mark document as read by user.
     */
    public function markAsRead($user)
    {
        return $this->readReceipts()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_position' => $user->position,
                'ip_address' => request()->ip(),
                'read_at' => now(),
            ]
        );
    }

    /**
     * Get the next pending approver.
     */
    public function getNextApprover()
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('sequence')
            ->first();
    }

    /**
     * Check if document is overdue.
     */
    public function isOverdue()
    {
        return $this->deadline && $this->deadline->isPast();
    }

    /**
     * Increment document version.
     */
    public function incrementVersion($major = false)
    {
        $parts = explode('.', $this->version);
        
        if ($major) {
            $parts[0] = (int)$parts[0] + 1;
            $parts[1] = 0;
        } else {
            $parts[1] = ((int)($parts[1] ?? 0)) + 1;
        }
        
        $this->version = implode('.', $parts);
        $this->save();
        
        return $this->version;
    }
}
