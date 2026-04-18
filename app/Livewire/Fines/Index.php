<?php

namespace App\Livewire\Fines;

use App\Models\Income;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }

    public function delete($id): void
    {
        $fine = Income::where('category', 'fine')->findOrFail($id);
        $fine->delete();
        session()->flash('success', 'Fine deleted.');
    }

    public function render()
    {
        $fines = Income::with('member')
            ->where('category', 'fine')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('fine_type', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhereHas('member', fn ($m) =>
                          $m->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%")
                      );
                });
            })
            ->orderByDesc('received_at')
            ->paginate($this->perPage);

        return view('livewire.fines.index', ['fines' => $fines]);
    }
}
