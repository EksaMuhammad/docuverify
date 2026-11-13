<?php

namespace App\Livewire;

use App\Models\Document;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

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
        $query = Document::with(['registrar', 'latestTransaction'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('document_code', 'like', $term)
                        ->orWhere('document_name', 'like', $term)
                        ->orWhere('document_hash', 'like', $term);
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('registration_status', $this->statusFilter);
            })
            ->latest();

        $documents = $query->paginate(10);

        return view('livewire.document-management', compact('documents'))
            ->layout('layouts.admin', ['title' => 'Document Management']);
    }
}

