@props(['record'])

@php
    // Month-based overdue logic
    $isOverdue = $record?->due_at ? $record->due_at->copy()->endOfMonth()->isPast() : false;

    if ($record?->repaid) {
        $color = 'bg-green-100 border-green-500 text-green-700';
        $icon = '✔';
        $label = 'Fully Repaid';
        $desc = 'This loan has been fully settled.';
    } elseif ($isOverdue) {
        $color = 'bg-red-100 border-red-500 text-red-700';
        $icon = '❗';
        $label = 'Overdue';
        $desc = 'The due month has passed. Immediate action required.';
    } else {
        $color = 'bg-amber-100 border-amber-500 text-amber-700';
        $icon = '⏳';
        $label = 'Ongoing';
        $desc = 'This loan is currently active and ongoing.';
    }
@endphp

<div class="rounded-none border-l-4 p-4 shadow-sm {{ $color }}">
    <div class="flex items-center gap-3">
        <div class="text-3xl font-bold leading-none">
            {{ $icon }}
        </div>
        <div>
            <div class="text-xl font-bold">{{ $label }}</div>
            <div class="text-sm opacity-80">{{ $desc }}</div>
        </div>
    </div>
</div>