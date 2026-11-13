<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
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
        $transactions = Transaction::with('document')
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('transaction_hash', 'like', $term)
                        ->orWhereHas('document', function ($d) use ($term) {
                            $d->where('document_code', 'like', $term)
                              ->orWhere('document_name', 'like', $term);
                        });
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('transaction_status', $this->statusFilter);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.transaction-history', compact('transactions'))
            ->layout('layouts.admin', ['title' => 'Blockchain Transactions']);
    }
}

