<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'transaction_hash',
        'transaction_type',
        'network',
        'gas_used',
        'gas_price',
        'block_number',
        'transaction_status',
        'raw_receipt',
        'confirmed_at',
    ];

    protected $casts = [
        'raw_receipt' => 'array',
        'gas_used' => 'integer',
        'block_number' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get explorer URL if Sepolia or standard explorer is configured.
     */
    public function getExplorerUrlAttribute(): ?string
    {
        $base = config('services.blockchain.etherscan_tx_url', 'https://sepolia.etherscan.io/tx/');
        return $this->transaction_hash ? rtrim($base, '/') . '/' . $this->transaction_hash : null;
    }
}

