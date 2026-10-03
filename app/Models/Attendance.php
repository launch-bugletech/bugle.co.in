<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in_at',
        'clock_out_at',
        'clock_in_photo',
        'clock_out_photo',
        'clock_in_ip',
        'clock_out_ip',
        'clock_in_user_agent',
        'clock_out_user_agent',
        'clock_in_lat',
        'clock_in_lng',
        'clock_out_lat',
        'clock_out_lng',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
