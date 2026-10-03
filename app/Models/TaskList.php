<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/TaskList.php
class TaskList extends Model
{
    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id'); // change 'user_id' to your actual FK
}
}

