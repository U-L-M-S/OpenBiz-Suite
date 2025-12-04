<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'leave_type_id',
        'approved_by',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'rejection_reason',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($leaveRequest) {
            if ($leaveRequest->start_date && $leaveRequest->end_date) {
                $start = Carbon::parse($leaveRequest->start_date);
                $end = Carbon::parse($leaveRequest->end_date);
                $leaveRequest->total_days = $start->diffInDays($end) + 1;
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function approve(User $user): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Update leave balance
        $this->updateLeaveBalance();
    }

    public function reject(User $user, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'rejection_reason' => $reason,
        ]);
    }

    public function cancel(): void
    {
        $oldStatus = $this->status;
        $this->update(['status' => 'cancelled']);

        // If it was approved, restore leave balance
        if ($oldStatus === 'approved') {
            $this->restoreLeaveBalance();
        }
    }

    protected function updateLeaveBalance(): void
    {
        $year = $this->start_date->year;

        $balance = LeaveBalance::firstOrCreate(
            [
                'tenant_id' => $this->tenant_id,
                'employee_id' => $this->employee_id,
                'leave_type_id' => $this->leave_type_id,
                'year' => $year,
            ],
            [
                'total_days' => $this->leaveType->default_days_per_year,
                'used_days' => 0,
                'remaining_days' => $this->leaveType->default_days_per_year,
            ]
        );

        $balance->used_days += $this->total_days;
        $balance->remaining_days = $balance->total_days - $balance->used_days;
        $balance->save();
    }

    protected function restoreLeaveBalance(): void
    {
        $year = $this->start_date->year;

        $balance = LeaveBalance::where([
            'tenant_id' => $this->tenant_id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $year,
        ])->first();

        if ($balance) {
            $balance->used_days -= $this->total_days;
            $balance->remaining_days = $balance->total_days - $balance->used_days;
            $balance->save();
        }
    }
}
