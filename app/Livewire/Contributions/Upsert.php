<?php

namespace App\Livewire\Contributions;

use App\Models\Contribution;
use App\Models\Income;
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
    public $contribution_period;
    public $payment_method = 'mpesa';
    public $notes = '';

    // Data
    public $members = [];

    protected function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'paid_at' => 'required|date',
            'contribution_period' => 'required|date',
            'shares' => 'required|numeric|min:0',
            'welfare' => 'required|numeric|min:0',
            'merry_go_round' => 'required|numeric|min:0',
            'penalty' => 'required|numeric|min:0',
            'penalty_type' => 'nullable|string|max:255',
            'payment_method' => 'required|in:mpesa,zimele,merry_go_round',
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
            $this->contribution_period = $contribution->contribution_period ? $contribution->contribution_period->format('Y-m') : '';
            $this->shares = $contribution->shares;
            $this->welfare = $contribution->welfare;
            $this->merry_go_round = $contribution->merry_go_round;
            $this->penalty = $contribution->penalty;
            $this->penalty_type = $contribution->penalty_type ?? '';
            $this->payment_method = $contribution->payment_method ?? 'mpesa';
            $this->notes = $contribution->notes ?? '';
        } else {
            $this->paid_at = date('Y-m-d');
            $this->contribution_period = date('Y-m');
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

        if ($propertyName === 'penalty_type') {
            $this->penalty = match ($this->penalty_type) {
                'lateness' => 100,
                'absenteeism' => 200,
                default => 0,
            };
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Convert contribution_period from YYYY-MM to YYYY-MM-01 format, ensuring valid date
        if (!empty($validated['contribution_period'])) {
            $validated['contribution_period'] = date('Y-m-01', strtotime($validated['contribution_period']));
        }

        if ($this->contribution) {
            // On edit, check no other contribution exists for this member+month (excluding current)
            $duplicate = Contribution::where('member_id', $validated['member_id'])
                ->where('contribution_period', $validated['contribution_period'])
                ->where('id', '!=', $this->contribution->id)
                ->exists();

            if ($duplicate) {
                $this->addError('contribution_period', 'This member already has a contribution for this month.');
                return;
            }

            $this->contribution->update($validated);

            // Sync fine record: remove old one and create new if penalty > 0
            Income::where('category', 'fine')
                ->where('member_id', $this->contribution->member_id)
                ->where('description', 'like', 'Contribution #' . $this->contribution->id . '%')
                ->delete();

            if ((float) $validated['penalty'] > 0) {
                Income::create([
                    'amount'      => $validated['penalty'],
                    'received_at' => $validated['paid_at'],
                    'category'    => 'fine',
                    'fine_type'   => $validated['penalty_type'] ?? null,
                    'member_id'   => $validated['member_id'],
                    'description' => 'Contribution #' . $this->contribution->id . ' – ' . ucfirst($validated['penalty_type'] ?? 'fine'),
                ]);
            }

            session()->flash('success', 'Contribution updated successfully.');
        } else {
            // Block duplicate: one contribution per member per month
            $duplicate = Contribution::where('member_id', $validated['member_id'])
                ->where('contribution_period', $validated['contribution_period'])
                ->exists();

            if ($duplicate) {
                $this->addError('contribution_period', 'This member already has a contribution for this month. Edit the existing one instead.');
                return;
            }

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
                Income::create([
                    'amount'      => $contribution->penalty,
                    'received_at' => $contribution->paid_at,
                    'category'    => 'fine',
                    'fine_type'   => $contribution->penalty_type ?? null,
                    'member_id'   => $contribution->member_id,
                    'description' => 'Contribution #' . $contribution->id . ' – ' . ucfirst($contribution->penalty_type ?? 'fine'),
                ]);

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
