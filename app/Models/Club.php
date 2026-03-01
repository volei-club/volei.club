<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Club extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'name',
        'created_by'
    ];

    /**
     * Get the user who created the club.
     */
    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    /**
     * Get the teams for the club.
     */
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the users associated with the club (Managers, Coaches, Parents, Athletes).
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
