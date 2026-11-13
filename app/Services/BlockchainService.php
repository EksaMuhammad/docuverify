<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockchainService
{
    protected string $serviceUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->serviceUrl = rtrim(config('services.blockchain.service_url', 'http://127.0.0.1:3001'), '/');
        $this->timeout = 15; // 15 seconds timeout
    }

    /**
     * Check health of blockchain integration microservice and RPC node.
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(3)->get("{$this->serviceUrl}/health");
            if ($response->successful()) {
                return $response->json();
            }
            return [
                'status' => 'error',
                'connected' => false,
                'message' => 'Service returned status ' . $response->status(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'connected' => false,
                'message' => 'Failed to reach blockchain service: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query network status (gas price, latest block, latency).
     */
    public function getNetworkStatus(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/api/network-status");
            if ($response->successful()) {
                return $response->json();
            }
            return [
                'success' => false,
                'status' => 'TECHNICAL_ERROR',
                'message' => 'Network status endpoint error: ' . $response->body(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'TECHNICAL_ERROR',
                'message' => 'Blockchain service unreachable: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Register a document on the smart contract.
     */
    public function registerDocument(string $documentCode, string $documentHash): array
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->serviceUrl}/api/documents/register", [
                'documentCode' => $documentCode,
                'documentHash' => $documentHash,
            ]);

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? false)) {
                return [
                    'success' => true,
                    'status' => 'CONFIRMED',
                    'documentCode' => $body['documentCode'] ?? $documentCode,
                    'documentId' => $body['documentId'] ?? null,
                    'documentHash' => $body['documentHash'] ?? $documentHash,
                    'transactionHash' => $body['transactionHash'] ?? null,
                    'blockNumber' => $body['blockNumber'] ?? null,
                    'gasUsed' => $body['gasUsed'] ?? null,
                    'gasPrice' => $body['gasPrice'] ?? null,
                    'network' => $body['network'] ?? 'unknown',
                    'registrant' => $body['registrant'] ?? null,
                    'registeredAt' => $body['registeredAt'] ?? now()->toIso8601String(),
                ];
            }

            // Document already registered
            if ($response->status() === 409) {
                return [
                    'success' => false,
                    'status' => 'ALREADY_REGISTERED',
                    'message' => $body['message'] ?? 'Document code is already registered on blockchain.',
                    'documentId' => $body['documentId'] ?? null,
                ];
            }

            return [
                'success' => false,
                'status' => 'TRANSACTION_FAILED',
                'message' => $body['message'] ?? 'Registration transaction failed on blockchain.',
                'code' => $body['code'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Blockchain registration exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 'TECHNICAL_ERROR',
                'message' => 'Connection to blockchain service timed out or failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify document hash against blockchain record.
     * Guaranteed to return one of: VALID, MODIFIED, UNVERIFIABLE, TECHNICAL_ERROR.
     */
    public function verifyDocument(string $documentCode, string $uploadedHash): array
    {
        try {
            $response = Http::timeout(8)->post("{$this->serviceUrl}/api/documents/verify", [
                'documentCode' => $documentCode,
                'uploadedHash' => $uploadedHash,
            ]);

            $body = $response->json() ?? [];

            if ($response->successful() && isset($body['verificationStatus'])) {
                return [
                    'success' => true,
                    'verificationStatus' => $body['verificationStatus'], // VALID, MODIFIED, or UNVERIFIABLE
                    'documentCode' => $body['documentCode'] ?? $documentCode,
                    'documentId' => $body['documentId'] ?? null,
                    'uploadedHash' => $body['uploadedHash'] ?? $uploadedHash,
                    'blockchainHash' => $body['blockchainHash'] ?? null,
                    'registeredAt' => $body['registeredAt'] ?? null,
                    'registrant' => $body['registrant'] ?? null,
                    'message' => $body['message'] ?? '',
                ];
            }

            // HTTP error from service or explicit technical failure
            return [
                'success' => false,
                'verificationStatus' => 'TECHNICAL_ERROR',
                'documentCode' => $documentCode,
                'uploadedHash' => $uploadedHash,
                'message' => $body['message'] ?? 'Blockchain service communication failure.',
            ];
        } catch (Exception $e) {
            Log::error('Blockchain verification exception: ' . $e->getMessage());
            return [
                'success' => false,
                'verificationStatus' => 'TECHNICAL_ERROR',
                'documentCode' => $documentCode,
                'uploadedHash' => $uploadedHash,
                'message' => 'Blockchain network error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve document details from blockchain.
     */
    public function getDocumentRecord(string $documentCode): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->serviceUrl}/api/documents/" . urlencode($documentCode));
            return $response->json() ?? ['success' => false];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'TECHNICAL_ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }
}

