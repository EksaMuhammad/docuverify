<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $activities = ActivityLog::with('user')
            ->when($this->search !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('activity', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('ip_address', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.activity-logs', compact('activities'))
            ->layout('layouts.admin', ['title' => 'System Activity Logs']);
    }
}

