<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;

class AssetMaintenance extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'reported_by',
        'type',
        'description',
        'scheduled_date',
        'completed_date',
        'cost',
        'vendor',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function complete(?string $notes = null, ?float $cost = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'notes' => $notes ?? $this->notes,
            'cost' => $cost ?? $this->cost,
        ]);

        // If asset is in maintenance, return it to available
        if ($this->asset->status === 'maintenance') {
            $this->asset->update(['status' => 'available']);
        }
    }
}
