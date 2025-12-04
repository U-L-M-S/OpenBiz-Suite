<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportDefinition extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'data_source',
        'columns',
        'filters',
        'grouping',
        'sorting',
        'chart_config',
        'is_public',
        'is_scheduled',
        'schedule_cron',
        'created_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'grouping' => 'array',
        'sorting' => 'array',
        'chart_config' => 'array',
        'is_public' => 'boolean',
        'is_scheduled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->name) . '-' . Str::random(6);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    public function export(User $user, string $format = 'csv', ?array $parameters = null): ReportExport
    {
        return $this->exports()->create([
            'tenant_id' => $this->tenant_id,
            'requested_by' => $user->id,
            'format' => $format,
            'status' => 'pending',
            'parameters' => $parameters,
        ]);
    }
}
