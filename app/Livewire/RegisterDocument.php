<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Transaction;
use App\Services\BlockchainService;
use App\Services\DocumentHashService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegisterDocument extends Component
{
    use WithFileUploads;

    public string $documentName = '';
    public string $documentCode = '';
    public string $notes = '';
    public $file = null;

    public bool $isProcessing = false;
    public ?array $registrationResult = null;
    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'documentName' => ['required', 'string', 'min:3', 'max:255'],
            'documentCode' => ['required', 'string', 'min:4', 'max:64', 'unique:documents,document_code'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];
    }

    public function mount(DocumentHashService $hashService)
    {
        $this->documentCode = $hashService->generateDocumentCode();
    }

    public function generateNewCode(DocumentHashService $hashService)
    {
        $this->documentCode = $hashService->generateDocumentCode();
    }

    public function register(DocumentHashService $hashService, BlockchainService $blockchainService)
    {
        $this->errorMessage = null;
        $this->registrationResult = null;
        $this->validate();

        $this->isProcessing = true;
        $tempFilePath = null;

        try {
            // Validate PDF header and magic bytes
            $hashService->validatePdf($this->file);
            $fileSize = $this->file->getSize();

            // Compute SHA-256 byte fingerprint
            $documentHash = $hashService->computeSha256($this->file);
            $normalizedCode = $hashService->normalizeCode($this->documentCode);
            $documentId = $hashService->deriveDocumentId($normalizedCode);

            // 1. Save initial record in MySQL with pending status
            $document = Document::create([
                'document_code' => $normalizedCode,
                'document_name' => $this->documentName,
                'document_hash' => $documentHash,
                'document_id' => $documentId,
                'registered_by' => Auth::id(),
                'registration_status' => 'pending',
                'file_size' => $fileSize,
                'notes' => $this->notes,
            ]);

            // 2. Call blockchain service
            $txResult = $blockchainService->registerDocument($normalizedCode, $documentHash);

            if ($txResult['success'] && ($txResult['status'] === 'CONFIRMED')) {
                // Update document record with confirmed on-chain details
                $document->update([
                    'registration_status' => 'confirmed',
                    'transaction_hash' => $txResult['transactionHash'],
                    'block_number' => $txResult['blockNumber'],
                    'gas_used' => $txResult['gasUsed'],
                    'registered_at' => $txResult['registeredAt'] ? now()->parse($txResult['registeredAt']) : now(),
                ]);

                // Record transaction
                Transaction::create([
                    'document_id' => $document->id,
                    'transaction_hash' => $txResult['transactionHash'],
                    'transaction_type' => 'register',
                    'network' => $txResult['network'] ?? 'hardhat_local',
                    'gas_used' => $txResult['gasUsed'],
                    'gas_price' => $txResult['gasPrice'],
                    'block_number' => $txResult['blockNumber'],
                    'transaction_status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

                ActivityLog::log(
                    Auth::id(),
                    'REGISTER_DOCUMENT',
                    "Registered document '{$this->documentName}' ({$normalizedCode}) on blockchain.",
                    [
                        'document_id' => $document->id,
                        'transaction_hash' => $txResult['transactionHash'],
                        'document_hash' => $documentHash,
                    ]
                );

                $this->registrationResult = [
                    'status' => 'CONFIRMED',
                    'documentCode' => $normalizedCode,
                    'documentName' => $this->documentName,
                    'documentHash' => $documentHash,
                    'transactionHash' => $txResult['transactionHash'],
                    'blockNumber' => $txResult['blockNumber'],
                    'gasUsed' => $txResult['gasUsed'],
                    'network' => $txResult['network'] ?? 'hardhat_local',
                    'registeredAt' => $txResult['registeredAt'],
                ];

                $this->reset(['documentName', 'notes', 'file']);
                $this->documentCode = $hashService->generateDocumentCode();
            } else {
                // Registration failed on blockchain
                $document->update([
                    'registration_status' => 'failed',
                ]);

                if (!empty($txResult['transactionHash'])) {
                    Transaction::create([
                        'document_id' => $document->id,
                        'transaction_hash' => $txResult['transactionHash'],
                        'transaction_type' => 'register',
                        'network' => $txResult['network'] ?? 'hardhat_local',
                        'transaction_status' => 'failed',
                    ]);
                }

                $this->errorMessage = $txResult['message'] ?? 'Blockchain transaction rejected or could not be confirmed.';
            }
        } catch (Exception $e) {
            Log::error('Document registration exception: ' . $e->getMessage());
            $this->errorMessage = 'Registration failure: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;

            // Purge temporary upload file per specification
            if ($this->file && method_exists($this->file, 'getRealPath')) {
                $p = $this->file->getRealPath();
                if ($p && file_exists($p)) {
                    @unlink($p);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.register-document')
            ->layout('layouts.admin', ['title' => 'Register PDF Document']);
    }
}
