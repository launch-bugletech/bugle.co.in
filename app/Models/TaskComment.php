<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/TaskComment.php
class TaskComment extends Model
{
    protected $fillable = ['task_id','user_id','comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
