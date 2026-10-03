<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_list_id',
        'title',
        'description',
        'assigned_to',
        'priority',
        'status',
        'time_logs',
        'due_date',
        'position',
        'project_id',
        'task_category',
        'task_type',
    ];

    protected $casts = [
        'assigned_to' => 'array',
        'due_date' => 'date',
        'time_logs' => 'array',
    ];

    // ── Accessors ────────────────────────────────────────────────────

    public function getTotalTimeSpentAttribute(): int
    {
        $logs = $this->time_logs ?? [];
        return collect($logs)->sum(
            fn($log) =>
            isset($log['end']) ? ($log['end'] - $log['start']) : 0
        );
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            $this->status !== 'completed';
    }

    public function getIsDueTodayAttribute()
    {
        return $this->due_date && $this->due_date->isToday();
    }

    public function getPriorityColorAttribute()
    {
        return [
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
        ][$this->priority] ?? 'secondary';
    }

    public function getStatusColorAttribute()
    {
        return [
            'new' => 'primary',
            'in_progress' => 'warning',
            'completed' => 'success',
        ][$this->status] ?? 'secondary';
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('M d, Y') : null;
    }

    public function getRelativeDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->diffForHumans() : null;
    }

    public function getFirstAssignedUserAttribute()
    {
        return $this->assignedUsers->first();
    }

    // ── Relationships ────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user', 'task_id', 'user_id')
            ->withTimestamps();
    }

    public function getAssignedUsersAttribute()
    {
        if (empty($this->assigned_to) || !is_array($this->assigned_to)) {
            return collect();
        }

        $ids = array_map(fn($id) => is_string($id) ? (int) $id : $id, $this->assigned_to);

        return User::whereIn('id', $ids)->get();
    }

    public function remarks()
    {
        return $this->hasMany(TaskRemark::class)->with('user')->latest();
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereJsonContains('assigned_to', (string) $userId)
                ->orWhereJsonContains('assigned_to', (int) $userId);
        });
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriority($query, $priority)
    {
        return is_array($priority)
            ? $query->whereIn('priority', $priority)
            : $query->where('priority', $priority);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('task_type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isAssignedTo($userId): bool
    {
        if (empty($this->assigned_to) || !is_array($this->assigned_to)) {
            return false;
        }

        return in_array((string) $userId, $this->assigned_to) ||
            in_array((int) $userId, $this->assigned_to);
    }

    public function assignUser($userId): self
    {
        $assignedTo = $this->assigned_to ?? [];

        if (!in_array((string) $userId, $assignedTo)) {
            $assignedTo[] = (string) $userId;
            $this->assigned_to = $assignedTo;
            $this->save();
        }

        return $this;
    }

    public function unassignUser($userId): self
    {
        if (empty($this->assigned_to)) {
            return $this;
        }

        $assignedTo = array_filter(
            $this->assigned_to,
            fn($id) => $id != $userId && $id != (string) $userId
        );

        $this->assigned_to = array_values($assignedTo);
        $this->save();

        return $this;
    }

    // ── Boot ─────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (is_null($task->position)) {
                $task->position = static::where('status', $task->status)->max('position') + 1;
            }
            if (empty($task->task_type)) {
                $task->task_type = 'one_time';
            }
        });

        static::deleting(function ($task) {
            static::where('status', $task->status)
                ->where('position', '>', $task->position)
                ->decrement('position');
        });
    }
}