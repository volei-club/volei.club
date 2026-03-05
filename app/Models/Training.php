<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Training extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $fillable = [
        'club_id',
        'location_id',
        'team_id',
        'squad_id',
        'coach_id',
        'day_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    /**
     * Get the club that owns the training.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the location for the training.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the team for the training.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the squad for the training.
     */
    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * Get the coach (user) for the training.
     */
    public function coach()
    {
        return $this->belongsTo(User::class , 'coach_id');
    }

    /**
     * Get attendance records for this training.
     */
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    /**
     * Get cancellations for this training.
     */
    public function cancellations()
    {
        return $this->hasMany(TrainingCancellation::class);
    }
}
