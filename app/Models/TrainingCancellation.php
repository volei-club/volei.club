<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'date',
        'reason',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
