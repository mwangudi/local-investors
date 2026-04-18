<?php

namespace App\Livewire\Fines;

use App\Models\Income;
use App\Models\Member;
use App\Notifications\InAppNotification;
use App\Services\NotifyService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Income $fine = null;

    public $member_id;
    public $amount;
    public $fine_type = 'late_attendance';
    public $description;
    public $received_at;

    protected function rules(): array
    {
        return [
            'member_id'   => 'required|exists:members,id',
            'amount'      => 'required|numeric|min:0.01',
            'fine_type'   => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'received_at' => 'required|date',
        ];
    }

    public function mount(?Income $fine = null): void
    {
        if ($fine && $fine->exists) {
            abort_unless($fine->category === 'fine', 404);
            $this->fine        = $fine;
            $this->member_id   = $fine->member_id;
            $this->amount      = $fine->amount;
            $this->fine_type   = $fine->fine_type;
            $this->description = $fine->description;
            $this->received_at = $fine->received_at?->format('Y-m-d');
        } else {
            $this->received_at = now()->format('Y-m-d');
            $this->amount      = 100; // Default: late attendance fine (KES 100)
        }
    }

    /**
     * Auto-fill amount based on fine type.
     */
    public function updatedFineType($value): void
    {
        if (! $this->fine) {
            $this->amount = match ($value) {
                'late_attendance' => 100,
                'absent'          => 200,
                default           => $this->amount,
            };
        }
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['category'] = 'fine';

        if ($this->fine) {
            $this->fine->update($data);
            session()->flash('success', 'Fine updated.');
        } else {
            $fine = Income::create($data);

            NotifyService::toMember($fine->member_id, new InAppNotification(
                type:    'fine.issued',
                title:   'Fine issued',
                message: 'A fine of KES ' . number_format((float) $fine->amount, 2)
                       . ' was recorded (' . $fine->fine_type . ').',
                url:     route('fines.index'),
                icon:    'feather-alert-triangle',
                color:   'warning',
            ));
            session()->flash('success', 'Fine recorded.');
        }

        $this->redirect(route('fines.index'));
    }

    public function render()
    {
        return view('livewire.fines.upsert', [
            'members' => Member::orderBy('first_name')->get(),
        ]);
    }
}
