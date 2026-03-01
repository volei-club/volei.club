<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditEvent('created');
        });

        static::updated(function ($model) {
            $model->auditEvent('updated');
        });

        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });
    }

    /**
     * Create an audit log record for a given event.
     */
    protected function auditEvent($event)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $newValues = $this->getDirty();
            foreach ($newValues as $key => $value) {
                // Nu vrem sa inregistram updated_at pt ca polueaza aiurea un update
                if ($key !== 'updated_at') {
                    $oldValues[$key] = $this->getOriginal($key);
                }
                else {
                    unset($newValues[$key]);
                }
            }
        }
        elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        }
        elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        // Daca e update si nicio valoare relevanta nu s-a schimbat, ignoram
        if ($event === 'updated' && empty($newValues)) {
            return;
        }

        $clubId = null;
        if (isset($this->club_id)) {
            $clubId = $this->club_id;
        }
        elseif (Auth::check()) {
            $clubId = Auth::user()->club_id;
        }

        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'user_id' => Auth::id() ?? null,
            'club_id' => $clubId,
            'event' => $event,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Relationship to fetch audit logs for the current model.
     */
    public function audits()
    {
        return $this->morphMany(AuditLog::class , 'auditable')->latest();
    }
}
