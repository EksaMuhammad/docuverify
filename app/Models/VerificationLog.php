<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'document_code',
        'uploaded_hash',
        'blockchain_hash',
        'verification_status',
        'processing_time_ms',
        'ip_address',
        'user_agent',
        'error_message',
    ];

    protected $casts = [
        'processing_time_ms' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}

