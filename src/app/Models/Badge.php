<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Badge extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'type',
        'criteria',
        'points_reward',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($badge) {
            if (empty($badge->slug)) {
                $badge->slug = Str::slug($badge->name) . '-' . Str::random(6);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'metadata')
            ->withTimestamps();
    }

    public function awardTo(User $user, ?array $metadata = null): void
    {
        if (!$this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, [
                'earned_at' => now(),
                'metadata' => $metadata ? json_encode($metadata) : null,
            ]);

            if ($this->points_reward > 0) {
                UserPoints::create([
                    'tenant_id' => $this->tenant_id,
                    'user_id' => $user->id,
                    'points' => $this->points_reward,
                    'source_type' => self::class,
                    'source_id' => $this->id,
                    'description' => "Earned badge: {$this->name}",
                ]);
            }
        }
    }
}
