<?php

namespace App\Models\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait HasAuditLog
{
    use LogsActivity;

    /**
     * Get the options for logging activities.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getTable());
    }

    /**
     * Get attributes to log.
     */
    protected function getLogAttributes(): array
    {
        // Log all fillable attributes by default
        return $this->fillable ?? ['*'];
    }

    /**
     * Boot the trait and add tenant_id to activity logs.
     */
    protected static function bootHasAuditLog(): void
    {
        static::eventsToBeRecorded()->each(function ($event) {
            static::$event(function ($model) use ($event) {
                // Ensure activity log has tenant_id
                if (auth()->check() && auth()->user()->tenant_id) {
                    activity()
                        ->performedOn($model)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'tenant_id' => auth()->user()->tenant_id,
                            'attributes' => $model->attributesToBeLogged($event)['attributes'] ?? [],
                            'old' => $model->attributesToBeLogged($event)['old'] ?? [],
                        ])
                        ->tap(function ($activity) use ($model) {
                            $activity->tenant_id = auth()->user()->tenant_id ?? $model->tenant_id ?? null;
                        });
                }
            });
        });
    }
}
