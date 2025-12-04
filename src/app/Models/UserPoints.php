<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserPoints extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'points',
        'source_type',
        'source_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public static function awardPoints(User $user, int $points, Model $source, ?string $description = null): self
    {
        return static::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'points' => $points,
            'source_type' => get_class($source),
            'source_id' => $source->id,
            'description' => $description,
        ]);
    }

    public static function getTotalPoints(User $user): int
    {
        return static::where('user_id', $user->id)->sum('points');
    }
}
