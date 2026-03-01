<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    /**
     * Get the owning auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the changes.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
