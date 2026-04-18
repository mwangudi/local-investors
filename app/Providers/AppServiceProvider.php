<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Loan;
use App\Observers\UserObserver;
use App\Observers\LoanObserver;
use Illuminate\Support\Facades\Gate;
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
        Loan::observe(LoanObserver::class);
        User::observe(UserObserver::class);

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

        // An admin can do anything — no need to assign every permission.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
