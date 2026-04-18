<?php

namespace App\Livewire\Contributions;

use App\Models\Contribution;
use App\Models\Member;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Contribution $contribution = null;
    
    // Form fields
    public $member_id = '';
    public $shares = '';
    public $welfare = '';
    public $merry_go_round = '';
    public $penalty = '';
    public $penalty_type = '';
    public $paid_at;
    public $notes = '';

    // Data
    public $members = [];

    protected function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'paid_at' => 'required|date',
            'shares' => 'required|numeric|min:0',
            'welfare' => 'required|numeric|min:0',
            'merry_go_round' => 'required|numeric|min:0',
            'penalty' => 'required|numeric|min:0',
            'penalty_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function mount(?Contribution $contribution = null): void
    {
        $this->members = Member::orderBy('first_name')->get();

        if ($contribution && $contribution->exists) {
            $this->contribution = $contribution;
            $this->member_id = $contribution->member_id;
            $this->paid_at = $contribution->paid_at->format('Y-m-d');
            $this->shares = $contribution->shares;
            $this->welfare = $contribution->welfare;
            $this->merry_go_round = $contribution->merry_go_round;
            $this->penalty = $contribution->penalty;
            $this->penalty_type = $contribution->penalty_type ?? '';
            $this->notes = $contribution->notes ?? '';
        } else {
            $this->paid_at = date('Y-m-d');
            // Set defaults
            $this->shares = '';
            $this->welfare = '';
            $this->merry_go_round = '';
            $this->penalty = '';
        }
    }

    public function updated(string $propertyName): void
    {
        $this->resetErrorBag($propertyName);
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->contribution) {
            $this->contribution->update($validated);
            session()->flash('success', 'Contribution updated successfully.');
        } else {
            $contribution = Contribution::create($validated);

            $total = (float) $contribution->shares
                   + (float) $contribution->welfare
                   + (float) $contribution->merry_go_round
                   + (float) $contribution->penalty;

            NotifyService::toMember($contribution->member_id, new InAppNotification(
                type:    'contribution.recorded',
                title:   'Contribution recorded',
                message: 'KES ' . number_format($total, 2) . ' recorded on ' . \Carbon\Carbon::parse($contribution->paid_at)->format('Y-m-d') . '.',
                url:     route('contributions.index'),
                icon:    'feather-dollar-sign',
                color:   'success',
            ));

            if ((float) $contribution->penalty > 0) {
                NotifyService::toMember($contribution->member_id, new InAppNotification(
                    type:    'fine.issued',
                    title:   'Fine issued',
                    message: 'A fine of KES ' . number_format((float) $contribution->penalty, 2) . ' was recorded' .
                             ($contribution->penalty_type ? ' (' . $contribution->penalty_type . ')' : '') . '.',
                    url:     route('contributions.index'),
                    icon:    'feather-alert-triangle',
                    color:   'warning',
                ));
            }

            session()->flash('success', 'Contribution created successfully.');
        }

        $this->redirect(route('contributions.index'));
    }

    public function render()
    {
        return view('livewire.contributions.upsert');
    }
}
