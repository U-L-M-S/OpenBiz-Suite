<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;

class AssetAssignment extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'employee_id',
        'assigned_by',
        'assigned_date',
        'return_date',
        'status',
        'assignment_notes',
        'return_notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'return_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function returnAsset(?string $notes = null, string $status = 'returned'): void
    {
        $this->update([
            'return_date' => now(),
            'return_notes' => $notes,
            'status' => $status,
        ]);

        // Update asset status
        $this->asset->update(['status' => 'available']);
    }
}
