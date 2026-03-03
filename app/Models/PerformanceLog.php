<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $guarded = [];

    protected $casts = [
        'log_date' => 'date',
        'weight' => 'float',
        'vertical_jump' => 'float',
        'serve_speed' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class , 'coach_id');
    }
}
