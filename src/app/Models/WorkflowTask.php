<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_instance_id',
        'step_name',
        'task_type',
        'assigned_to',
        'assigned_role',
        'status',
        'input_data',
        'output_data',
        'notes',
        'due_date',
        'started_at',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(User $user, ?array $outputData = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'completed',
            'output_data' => $outputData,
            'notes' => $notes,
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        WorkflowHistory::create([
            'workflow_instance_id' => $this->workflow_instance_id,
            'workflow_task_id' => $this->id,
            'action' => 'task_completed',
            'performed_by' => $user->id,
            'data' => $outputData,
            'comment' => $notes,
        ]);
    }
}
