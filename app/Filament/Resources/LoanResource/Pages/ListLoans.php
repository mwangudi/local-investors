<?php

namespace App\Filament\Resources\LoanResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\LoanResource;
use App\Filament\Traits\HasIconizedFormActions;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Loan')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        $memberId = $user->member_id;

        $tabs = [
            'all' => \Filament\Resources\Components\Tab::make('All Loans'),
        ];

        if ($memberId) {
            $tabs['needs_approval'] = \Filament\Resources\Components\Tab::make('Needs My Approval')
                ->modifyQueryUsing(
                    fn(\Illuminate\Database\Eloquent\Builder $query) => $query
                        ->whereIn('status', [\App\Models\Loan::STATUS_APPLIED, \App\Models\Loan::STATUS_APPROVED])
                        ->where('member_id', '!=', $memberId)
                        ->whereDoesntHave('approvals', fn($q) => $q->where('member_id', $memberId))
                )
                ->badge(\App\Models\Loan::whereIn('status', [\App\Models\Loan::STATUS_APPLIED, \App\Models\Loan::STATUS_APPROVED])
                    ->where('member_id', '!=', $memberId)
                    ->whereDoesntHave('approvals', fn($q) => $q->where('member_id', $memberId))
                    ->count());
        }

        return $tabs;
    }
}
