<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_code',
        'document_name',
        'document_hash',
        'document_id',
        'registered_by',
        'transaction_hash',
        'block_number',
        'gas_used',
        'registered_at',
        'registration_status',
        'file_size',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'block_number' => 'integer',
        'gas_used' => 'integer',
        'file_size' => 'integer',
    ];

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }

    /**
     * Normalize a document code for deterministic hashing.
     */
    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }
}

