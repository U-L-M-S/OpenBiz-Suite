<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowHistory extends Model
{
    use HasFactory;

    protected $table = 'workflow_history';

    protected $fillable = [
        'workflow_instance_id',
        'workflow_task_id',
        'action',
        'from_step',
        'to_step',
        'performed_by',
        'data',
        'comment',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'workflow_task_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
