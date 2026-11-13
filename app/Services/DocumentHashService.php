<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use kornrunner\Keccak;
use RuntimeException;

class DocumentHashService
{
    /**
     * Compute the SHA-256 hash of a file's byte contents.
     */
    public function computeSha256(string|UploadedFile $file): string
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidArgumentException("File not found or unreadable at: {$path}");
        }

        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            throw new RuntimeException("Failed to compute SHA-256 for file at: {$path}");
        }

        return strtolower($hash);
    }

    /**
     * Normalize a document code and derive the bytes32 documentId via keccak256.
     */
    public function deriveDocumentId(string $documentCode): string
    {
        $normalized = $this->normalizeCode($documentCode);
        return '0x' . strtolower(Keccak::hash($normalized, 256));
    }

    /**
     * Normalize document code: trimmed, uppercase UTF-8.
     */
    public function normalizeCode(string $documentCode): string
    {
        return mb_strtoupper(trim($documentCode), 'UTF-8');
    }

    /**
     * Generate a unique, formatted document code: DOC-YYYYMMDD-XXXX.
     */
    public function generateDocumentCode(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex characters
        return "DOC-{$date}-{$random}";
    }

    /**
     * Validate that an uploaded file is a genuine PDF.
     * Verifies MIME type and PDF magic bytes (%PDF-).
     */
    public function validatePdf(UploadedFile $file, int $maxSizeBytes = 20971520): void // 20 MB max
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException("File upload error: {$file->getErrorMessage()}");
        }

        if ($file->getSize() > $maxSizeBytes) {
            throw new InvalidArgumentException('File size exceeds the 20MB limit.');
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            throw new InvalidArgumentException('File must have a .pdf extension.');
        }

        // Check magic bytes (%PDF-)
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Cannot open uploaded file for validation.');
        }

        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            throw new InvalidArgumentException('The uploaded file is not a valid PDF document header.');
        }
    }
}

