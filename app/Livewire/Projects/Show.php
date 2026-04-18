<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function render()
    {
        $project = $this->project;

        $withdrawals  = $project->withdrawals()->orderBy('withdrawn_at')->get();
        $expenditures = $project->expenditures()->orderBy('spent_at')->get();
        $cashReturns  = $project->cashReturns()->orderBy('returned_at')->get();

        $totalIncome      = (float) $withdrawals->sum('amount');
        $totalExpenditure = (float) $expenditures->sum('amount');
        $totalReturned    = (float) $cashReturns->sum('amount');
        $balance          = $totalIncome - $totalExpenditure - $totalReturned;

        // Group expenditures by category
        $byCategory = $expenditures->groupBy('category')->map(fn ($items) => $items->sum('amount'));

        return view('livewire.projects.show', [
            'withdrawals'      => $withdrawals,
            'expenditures'     => $expenditures,
            'cashReturns'      => $cashReturns,
            'totalIncome'      => $totalIncome,
            'totalExpenditure' => $totalExpenditure,
            'totalReturned'    => $totalReturned,
            'balance'          => $balance,
            'byCategory'       => $byCategory,
        ]);
    }
}
