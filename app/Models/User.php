<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'club_id',
        'is_active',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    /**
     * Get the club the user belongs to.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * The teams the user belongs to (as an athlete or coach).
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Get the parents of this user (if student).
     */
    public function parents()
    {
        return $this->belongsToMany(User::class , 'parent_student', 'student_id', 'parent_id');
    }

    /**
     * Get the children of this user (if parent).
     */
    public function children()
    {
        return $this->belongsToMany(User::class , 'parent_student', 'parent_id', 'student_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
