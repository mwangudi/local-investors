@php
$colors = [
    'active' => 'bg-soft-success text-success',
    'inactive' => 'bg-soft-secondary text-secondary',
    'pending' => 'bg-soft-warning text-warning',
    'new' => 'bg-soft-info text-info',
    'contacted' => 'bg-soft-primary text-primary',
    'qualified' => 'bg-soft-success text-success',
    'converted' => 'bg-soft-success text-success',
    'lost' => 'bg-soft-danger text-danger',
    'in_progress' => 'bg-soft-primary text-primary',
    'completed' => 'bg-soft-success text-success',
    'on_hold' => 'bg-soft-warning text-warning',
    'cancelled' => 'bg-soft-danger text-danger',
];
$colorClass = $colors[$status] ?? 'bg-soft-secondary text-secondary';
@endphp

<span class="badge {{ $colorClass }} px-2 py-1 text-uppercase">{{ str_replace('_', ' ', $status) }}</span>
