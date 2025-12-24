<?php

namespace App\View\Components\Filament;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LoanStatus extends Component
{
    public $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function render(): View|Closure|string
    {
        return view('components.filament.loan-status');
    }
}
