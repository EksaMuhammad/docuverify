<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(?int $userId, string $activity, ?string $description = null, ?array $properties = null, ?string $ip = null): self
    {
        return self::create([
            'user_id' => $userId,
            'activity' => $activity,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => $ip ?? request()->ip(),
        ]);
    }
}

