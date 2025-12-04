<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WorkflowDefinition extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'entity_type',
        'steps',
        'transitions',
        'is_active',
        'version',
    ];

    protected $casts = [
        'steps' => 'array',
        'transitions' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($definition) {
            if (empty($definition->slug)) {
                $definition->slug = Str::slug($definition->name) . '-' . Str::random(6);
            }
        });
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    public function createInstance(Model $entity, ?User $startedBy = null): WorkflowInstance
    {
        $firstStep = collect($this->steps)->first();

        return $this->instances()->create([
            'tenant_id' => $this->tenant_id,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'current_step' => $firstStep['name'] ?? 'start',
            'status' => 'pending',
            'started_by' => $startedBy?->id,
            'started_at' => now(),
        ]);
    }

    public function getStepConfig(string $stepName): ?array
    {
        return collect($this->steps)->firstWhere('name', $stepName);
    }

    public function getAvailableTransitions(string $fromStep): array
    {
        return collect($this->transitions)
            ->where('from', $fromStep)
            ->values()
            ->toArray();
    }
}
