<?php

namespace App\Filament\Plugins;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Panel;
use Filament\Contracts\Plugin;

class DisableHtmlValidation implements Plugin
{
    public function getId(): string
    {
        return 'disable-html-validation';
    }

    public static function make(): static
    {
        return new static();
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        Component::configureUsing(function (Component $component) {

            // ONLY components that support HTML attributes
            $html5Inputs = [
                TextInput::class,
                DatePicker::class,
                DateTimePicker::class,
                Textarea::class,
                Select::class,
            ];

            foreach ($html5Inputs as $inputClass) {
                if ($component instanceof $inputClass) {
                    // Disable HTML5 validation
                    $component->extraInputAttributes([
                        'required' => false,
                        'min' => null,
                        'max' => null,
                        'pattern' => null,
                        'step' => null,
                    ]);
                }
            }

            // Enable Livewire inline validation globally
            $component->live(onBlur: true);
            
            // add error injection for components that have a state path
            if (method_exists($component, 'afterStateHydrated')) {

                $component->afterStateHydrated(function ($component, $state) {

                    $livewire = $component->getLivewire();
                    $statePath = $component->getStatePath();

                    $errors = $livewire->getErrorBag()->toArray();

                    if (array_key_exists($statePath, $errors)) {

                        // add "input-error" class
                        $component->extraAttributes([
                            'class' => 'input-error'
                        ]);

                        // add inline error message DOM
                        $component->helperText(
                            '<span class="filament-inline-error">' .
                            $errors[$statePath][0] .
                            '</span>'
                        );

                        // section highlight
                        $component->getContainer()->extraAttributes([
                            'class' => 'has-error'
                        ]);
                    }
                });
            }
        });
    }
}