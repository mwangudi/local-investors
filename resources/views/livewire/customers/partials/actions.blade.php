<div class="d-flex gap-2">
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-light-primary" data-bs-toggle="tooltip" title="Edit">
        <i class="feather-edit-2"></i>
    </a>
    <button type="button" class="btn btn-sm btn-light-danger" 
            wire:click="delete({{ $customer->id }})"
            wire:confirm="Are you sure you want to delete this customer?"
            data-bs-toggle="tooltip" title="Delete">
        <i class="feather-trash-2"></i>
    </button>
</div>
