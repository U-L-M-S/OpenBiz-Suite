<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class LeaveBalance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'remaining_days' => 'decimal:1',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
