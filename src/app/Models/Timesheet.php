<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'approved_by',
        'date',
        'clock_in',
        'clock_out',
        'hours_worked',
        'overtime_hours',
        'notes',
        'status',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($timesheet) {
            if ($timesheet->clock_in && $timesheet->clock_out) {
                $clockIn = Carbon::parse($timesheet->clock_in);
                $clockOut = Carbon::parse($timesheet->clock_out);

                $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
                $regularHours = min($totalHours, 8);
                $overtimeHours = max($totalHours - 8, 0);

                $timesheet->hours_worked = round($regularHours, 2);
                $timesheet->overtime_hours = round($overtimeHours, 2);
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
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
    }

    public function reject(User $user, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'rejection_reason' => $reason,
        ]);
    }

    public function submit(): void
    {
        $this->update(['status' => 'submitted']);
    }
}
