<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasAuditLog;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, HasAuditLog;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'asset_code',
        'name',
        'description',
        'serial_number',
        'manufacturer',
        'model',
        'purchase_date',
        'purchase_price',
        'current_value',
        'warranty_expiry',
        'location',
        'status',
        'condition',
        'qr_code_path',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_code)) {
                $asset->asset_code = 'AST' . str_pad(
                    Asset::where('tenant_id', $asset->tenant_id)->count() + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });

        static::created(function ($asset) {
            $asset->generateQrCode();
        });

        static::deleting(function ($asset) {
            if ($asset->qr_code_path && Storage::exists($asset->qr_code_path)) {
                Storage::delete($asset->qr_code_path);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->where('status', 'active')->latest();
    }

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function generateQrCode(): void
    {
        $qrCodeData = json_encode([
            'asset_id' => $this->id,
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'category' => $this->category->name ?? 'N/A',
        ]);

        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($qrCodeData);

        $filename = 'qr-codes/' . $this->asset_code . '.png';
        Storage::put($filename, $qrCode);

        $this->update(['qr_code_path' => $filename]);
    }

    public function assignTo(Employee $employee, User $assignedBy, ?string $notes = null): AssetAssignment
    {
        // Return previous active assignments
        $this->assignments()->where('status', 'active')->update(['status' => 'returned', 'return_date' => now()]);

        // Create new assignment
        $assignment = $this->assignments()->create([
            'tenant_id' => $this->tenant_id,
            'employee_id' => $employee->id,
            'assigned_by' => $assignedBy->id,
            'assigned_date' => now(),
            'status' => 'active',
            'assignment_notes' => $notes,
        ]);

        // Update asset status
        $this->update(['status' => 'assigned']);

        return $assignment;
    }
}
