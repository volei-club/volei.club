<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Game extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'matches';

    protected $guarded = [];

    protected $casts = [
        'match_date' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class , 'match_players', 'match_id', 'user_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function starters()
    {
        return $this->players()->wherePivot('type', 'titular');
    }

    public function substitutes()
    {
        return $this->players()->wherePivot('type', 'rezerva');
    }
}
