<?php

namespace App\Livewire\Members;

use App\Models\Member;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Member $member = null;
    
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public ?string $join_date = null;
    public bool $is_active = true;
    public string $notification_preference = 'sms';

    protected function rules(): array
    {
        $emailRule = 'required|email|unique:members,email';
        if ($this->member) {
            $emailRule .= ',' . $this->member->id;
        }

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'required|string|max:20',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notification_preference' => 'required|in:sms,email,both,none',
        ];
    }

    public function mount(?Member $member = null): void
    {
        if ($member && $member->exists) {
            $this->member = $member;
            $this->first_name = $member->first_name;
            $this->last_name = $member->last_name;
            $this->email = $member->email;
            $this->phone = $member->phone ?? '';
            $this->join_date = $member->join_date ? $member->join_date->format('Y-m-d') : null;
            $this->is_active = $member->is_active;
            $this->notification_preference = $member->notification_preference ?? 'sms';
        } else {
            $this->join_date = date('Y-m-d');
        }
    }

    public function updated(string $propertyName): void
    {
        $this->resetErrorBag($propertyName);
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->member) {
            $this->member->update($validated);
            session()->flash('success', 'Member updated successfully.');
        } else {
            $this->member = Member::create($validated);
            session()->flash('success', 'Member created successfully.');
        }

        $this->redirect(route('members.index'));
    }

    public function render()
    {
        return view('livewire.members.upsert');
    }
}
