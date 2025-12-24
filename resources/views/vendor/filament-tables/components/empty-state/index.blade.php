@php
    use Filament\Support\Enums\Alignment;
@endphp

@props([
    'actions' => [],
    'description' => null,
    'heading',
    'icon',
])

<div
    {{ $attributes->class(['fi-ta-empty-state px-6 py-12']) }}
>
    <div
        class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center"
    >
        <div class="fi-ta-empty-state-icon-ctn mb-4 p-3">
            <img 
                src="{{ asset('images/no-data-icon.png') }}" 
                alt="No data found" 
                class="fi-ta-empty-state-icon h-16 w-auto"
            />
        </div>

        <x-filament-tables::empty-state.heading>
            {{ $heading }}
        </x-filament-tables::empty-state.heading>

        @if ($description)
            <x-filament-tables::empty-state.description class="mt-1">
                {{ $description }}
            </x-filament-tables::empty-state.description>
        @endif

        @if ($actions)
            <x-filament-tables::actions
                :actions="$actions"
                :alignment="Alignment::Center"
                wrap
                class="mt-6"
            />
        @endif
    </div>
</div>
