<?php

namespace App\Livewire;

use App\Services\VerificationService;
use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;

class VerifyDocument extends Component
{
    use WithFileUploads;

    public string $documentCode = '';
    public $file = null;
    public ?string $selectedFileName = null;
    public ?float $selectedFileSizeKb = null;
    public ?array $result = null;
    public bool $isVerifying = false;
    public ?string $errorMessage = null;

    protected $rules = [
        'documentCode' => ['required', 'string', 'min:3', 'max:64'],
        'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // max 20MB
    ];

    public function mount()
    {
        if (request()->has('code')) {
            $this->documentCode = request()->query('code');
        }
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
        if ($this->file) {
            try {
                $this->selectedFileName = $this->file->getClientOriginalName();
                $this->selectedFileSizeKb = round($this->file->getSize() / 1024, 1);
            } catch (\Throwable $e) {
                $this->selectedFileSizeKb = null;
            }
        }
    }

    public function verify(VerificationService $verificationService)
    {
        $this->errorMessage = null;
        $this->result = null;

        $this->validate();

        $this->isVerifying = true;

        try {
            $this->result = $verificationService->verify(
                $this->documentCode,
                $this->file,
                request()->ip(),
                request()->userAgent()
            );
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isVerifying = false;
            $this->file = null; // Purge reference so Livewire does not re-query deleted file metadata
        }
    }

    public function resetForm()
    {
        $this->reset(['documentCode', 'file', 'selectedFileName', 'selectedFileSizeKb', 'result', 'errorMessage', 'isVerifying']);
    }

    public function render()
    {
        return view('livewire.verify-document')
            ->layout('layouts.public', ['title' => 'Verify PDF Integrity | DocuVerify']);
    }
}
