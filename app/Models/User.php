<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'role_id',
        'department',
        'location',
        'date_of_join',
        'date_of_left',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_join'      => 'date',
        'date_of_left'      => 'date',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => 'active',
    ];

    // app/Models/User.php

public function isAdmin()
{
    return $this->role === 'Admin';
}





    public function canManageTasks()
    {
        return $this->hasRole('Admin');
    }

    public function isEmployee()
    {
        return $this->hasRole('Employee');
    }
}
