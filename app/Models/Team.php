<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Team extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'club_id',
        'name'
    ];

    /**
     * Get the club that owns the team.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * The users (athletes and coaches) that belong to the team.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role'); // Optionally, team_user could store specific roles
    }
}
