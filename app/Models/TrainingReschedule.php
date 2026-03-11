<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingReschedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'training_id',
        'original_date',
        'new_date',
        'new_start_time',
        'new_end_time',
        'reason',
    ];

    protected $casts = [
        'original_date' => 'date:Y-m-d',
        'new_date' => 'date:Y-m-d',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
