<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'attachment'
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function progress()
    {
        $total = $this->tasks()->count();

        if ($total == 0) {
            return 0;
        }

        $completed = $this->tasks()
            ->whereIn('status', ['Completed', 'Finished'])
            ->count();

        return round(($completed / $total) * 100);
    }
    
    public function files()
{
    return $this->hasMany(ProjectFile::class);
}

}
