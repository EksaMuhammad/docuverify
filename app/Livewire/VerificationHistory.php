<?php

namespace App\Livewire;

use App\Models\VerificationLog;
use Livewire\Component;
use Livewire\WithPagination;

class VerificationHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = VerificationLog::with('document')
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('document_code', 'like', $term)
                        ->orWhere('uploaded_hash', 'like', $term)
                        ->orWhere('ip_address', 'like', $term);
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('verification_status', $this->statusFilter);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.verification-history', compact('logs'))
            ->layout('layouts.admin', ['title' => 'Verification Audit Trail']);
    }
}
