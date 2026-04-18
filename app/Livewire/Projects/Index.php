<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function delete($id): void
    {
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Project deleted.');
    }

    public function render()
    {
        $projects = Project::query()
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
            )
            ->when($this->statusFilter, fn ($q) =>
                $q->where('status', $this->statusFilter)
            )
            ->withSum('withdrawals', 'amount')
            ->withSum('expenditures', 'amount')
            ->withSum('cashReturns', 'amount')
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.projects.index', ['projects' => $projects]);
    }
}
