<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Upsert extends Component
{
    public ?Customer $customer = null;
    
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';
    public string $status = 'active';

    protected function rules(): array
    {
        $emailRule = 'required|email|unique:customers,email';
        if ($this->customer) {
            $emailRule .= ',' . $this->customer->id;
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,pending',
        ];
    }

    public function mount(?Customer $customer = null): void
    {
        if ($customer && $customer->exists) {
            $this->customer = $customer;
            $this->name = $customer->name;
            $this->email = $customer->email;
            $this->phone = $customer->phone ?? '';
            $this->company = $customer->company ?? '';
            $this->address = $customer->address ?? '';
            $this->city = $customer->city ?? '';
            $this->country = $customer->country ?? '';
            $this->status = $customer->status;
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->customer) {
            $this->customer->update($validated);
            session()->flash('success', 'Customer updated successfully.');
        } else {
            Customer::create($validated);
            session()->flash('success', 'Customer created successfully.');
        }

        $this->redirect(route('customers.index'));
    }

    public function render()
    {
        return view('livewire.customers.upsert');
    }
}
