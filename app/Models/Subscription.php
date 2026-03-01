<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'club_id',
        'name',
        'price',
        'period',
    ];

    /**
     * Get the club that owns the subscription definition.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user subscriptions associated with this generic subscription.
     */
    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}
