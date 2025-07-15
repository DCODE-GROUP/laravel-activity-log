<?php

namespace Workbench\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Dcodegroup\ActivityLog\Contracts\HasActivityUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasActivityUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];

    public function getActivityLogUserName(): string
    {
        return $this->name;
    }

    public function getActivityLogEmail(): string
    {
        return $this->email;
    }

    public function getActivityLogUser(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->getActivityLogUserName(),
            'email' => $this->getActivityLogEmail(),
        ];
    }
}
