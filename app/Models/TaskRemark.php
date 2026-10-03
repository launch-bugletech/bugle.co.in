<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskRemark extends Model
{
    protected $fillable = [
        'task_id', 'user_id', 'remark',
        'attachment_path', 'attachment_name', 'attachment_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}