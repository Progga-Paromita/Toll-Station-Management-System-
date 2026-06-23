<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'status',
        'login_attempts',
        'last_login',
        'created_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get user's role relation.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Helper checks for roles.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->role_name === 'ADMIN';
    }

    public function isOperator(): bool
    {
        return $this->role && $this->role->role_name === 'OPERATOR';
    }

    public function isViewer(): bool
    {
        return $this->role && $this->role->role_name === 'VIEWER';
    }
}
