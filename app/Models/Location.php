<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $guarded = [];

    /**
     * Get the club that owns the location.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
