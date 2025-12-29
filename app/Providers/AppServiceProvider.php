<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\Component;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Loan::observe(\App\Observers\LoanObserver::class);

        // Enable live validation for ALL Filament form components
        Component::configureUsing(function (Component $component) {
            if (method_exists($component, 'live')) {
                $component->live(onBlur: true); // validate on blur
            }
        });

        // Force validation to re-run live on field update
        Livewire::listen('property.updated', function ($component, $name, $value) {
            if (method_exists($component, 'validateOnly')) {
                try {
                    $component->validateOnly($name);
                } catch (\Throwable $e) {
                }
            }
        });
    }
}
