<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'workflow_definition_id',
        'entity_type',
        'entity_id',
        'current_step',
        'status',
        'data',
        'started_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class)->orderBy('created_at', 'desc');
    }

    public function transition(string $toStep, ?User $performedBy = null, ?string $comment = null): void
    {
        $fromStep = $this->current_step;

        WorkflowHistory::create([
            'workflow_instance_id' => $this->id,
            'action' => 'transition',
            'from_step' => $fromStep,
            'to_step' => $toStep,
            'performed_by' => $performedBy?->id,
            'comment' => $comment,
        ]);

        $this->update([
            'current_step' => $toStep,
            'status' => 'in_progress',
        ]);
    }

    public function complete(?User $performedBy = null, ?string $comment = null): void
    {
        WorkflowHistory::create([
            'workflow_instance_id' => $this->id,
            'action' => 'completed',
            'from_step' => $this->current_step,
            'performed_by' => $performedBy?->id,
            'comment' => $comment,
        ]);

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(?User $performedBy = null, ?string $comment = null): void
    {
        WorkflowHistory::create([
            'workflow_instance_id' => $this->id,
            'action' => 'cancelled',
            'from_step' => $this->current_step,
            'performed_by' => $performedBy?->id,
            'comment' => $comment,
        ]);

        $this->update(['status' => 'cancelled']);
    }
}
