<?php

namespace App\Services;

use App\Models\Document;
use App\Models\VerificationLog;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    public function __construct(
        protected DocumentHashService $hashService,
        protected BlockchainService $blockchainService
    ) {}

    /**
     * Perform end-to-end verification of an uploaded PDF against the blockchain.
     *
     * @param string $documentCode User-entered document code
     * @param UploadedFile $file The uploaded PDF file
     * @param string|null $ip Client IP address
     * @param string|null $userAgent Client User Agent
     * @return array Verification outcome and metadata
     */
    public function verify(string $documentCode, UploadedFile $file, ?string $ip = null, ?string $userAgent = null): array
    {
        $startTime = microtime(true);
        $normalizedCode = $this->hashService->normalizeCode($documentCode);
        $uploadedHash = null;
        $status = 'TECHNICAL_ERROR';
        $errorMessage = null;
        $blockchainHash = null;
        $blockchainMeta = [];
        $document = null;

        try {
            // 1. Validate PDF structure & magic bytes
            $this->hashService->validatePdf($file);

            // 2. Compute SHA-256 fingerprint
            $uploadedHash = $this->hashService->computeSha256($file);

            // 3. Optional DB lookup for supplementary presentation metadata (NOT for integrity truth!)
            $document = Document::where('document_code', $normalizedCode)->first();

            // 4. Query blockchain via BlockchainService
            $blockchainResult = $this->blockchainService->verifyDocument($normalizedCode, $uploadedHash);

            $status = $blockchainResult['verificationStatus'] ?? 'TECHNICAL_ERROR';
            $blockchainHash = $blockchainResult['blockchainHash'] ?? null;
            $blockchainMeta = [
                'registeredAt' => $blockchainResult['registeredAt'] ?? null,
                'registrant' => $blockchainResult['registrant'] ?? null,
                'message' => $blockchainResult['message'] ?? '',
            ];

            if ($status === 'TECHNICAL_ERROR') {
                $errorMessage = $blockchainResult['message'] ?? 'Blockchain service failure';
            }
        } catch (Exception $e) {
            Log::error("Verification failed for code {$normalizedCode}: " . $e->getMessage());
            $status = 'TECHNICAL_ERROR';
            $errorMessage = $e->getMessage();
        } finally {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            // Safely log the verification attempt
            if ($uploadedHash !== null) {
                try {
                    VerificationLog::create([
                        'document_id' => $document?->id,
                        'document_code' => $normalizedCode,
                        'uploaded_hash' => $uploadedHash,
                        'blockchain_hash' => $blockchainHash,
                        'verification_status' => $status,
                        'processing_time_ms' => $durationMs,
                        'ip_address' => $ip,
                        'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                        'error_message' => $errorMessage,
                    ]);
                } catch (Exception $logEx) {
                    Log::warning('Failed to record verification log: ' . $logEx->getMessage());
                }
            }

            // Unconditionally delete the temporary upload file if it still exists
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath)) {
                @unlink($realPath);
            }
        }

        return [
            'status' => $status, // VALID | MODIFIED | UNVERIFIABLE | TECHNICAL_ERROR
            'documentCode' => $normalizedCode,
            'uploadedHash' => $uploadedHash,
            'blockchainHash' => $blockchainHash,
            'documentName' => $document?->document_name,
            'registeredAt' => $blockchainMeta['registeredAt'] ?? $document?->registered_at?->toIso8601String(),
            'transactionHash' => $document?->transaction_hash,
            'blockNumber' => $document?->block_number,
            'durationMs' => $durationMs,
            'errorMessage' => $errorMessage,
        ];
    }
}
