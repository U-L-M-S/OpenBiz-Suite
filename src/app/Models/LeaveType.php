<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'default_days_per_year',
        'is_paid',
        'is_active',
        'color',
    ];

    protected $casts = [
        'default_days_per_year' => 'integer',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
