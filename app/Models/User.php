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
        'phone',
        'password',
        'role',
        'club_id',
        'is_active',
        'photo',
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

    public function squads()
    {
        return $this->belongsToMany(Squad::class);
    }

    /**
     * Get all subscriptions associated with this user.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the currently active subscription (paid or pending) that has already started and is not expired.
     */
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->whereIn('status', ['active_paid', 'active_pending'])
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->latest('id');
    }

    /**
     * Get the next upcoming subscription that starts in the future.
     */
    public function upcomingSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->whereIn('status', ['active_paid', 'active_pending'])
            ->where('starts_at', '>', now())
            ->orderBy('starts_at', 'asc');
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
