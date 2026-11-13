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
    public ?array $result = null;
    public bool $isVerifying = false;
    public ?string $errorMessage = null;

    protected $rules = [
        'documentCode' => ['required', 'string', 'min:3', 'max:64'],
        'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // max 20MB
    ];

    public function mount()
    {
        // Pre-fill from query string if available: e.g. /verify?code=DOC-123
        if (request()->has('code')) {
            $this->documentCode = request()->query('code');
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
        }
    }

    public function resetForm()
    {
        $this->reset(['documentCode', 'file', 'result', 'errorMessage', 'isVerifying']);
    }

    public function render()
    {
        return view('livewire.verify-document')
            ->layout('layouts.public', ['title' => 'Verify PDF Integrity | DocuVerify']);
    }
}
