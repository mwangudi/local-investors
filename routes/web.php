<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Crm;
use App\Livewire\Dashboard\Analytics;
use App\Livewire\Members\Index as MembersIndex;
use App\Livewire\Members\Upsert as MembersUpsert;
use App\Livewire\Contributions\Index as ContributionsIndex;
use App\Livewire\Contributions\Upsert as ContributionsUpsert;
use App\Livewire\Loans\Index as LoansIndex;
use App\Livewire\Loans\Upsert as LoansUpsert;
use App\Livewire\Loans\Show as LoansShow;
use App\Livewire\Expenditures\Index as ExpendituresIndex;
use App\Livewire\Expenditures\Upsert as ExpendituresUpsert;
use App\Livewire\Incomes\Index as IncomesIndex;
use App\Livewire\Incomes\Upsert as IncomesUpsert;
use App\Livewire\Withdrawals\Index as WithdrawalsIndex;
use App\Livewire\Withdrawals\Upsert as WithdrawalsUpsert;
use App\Livewire\CashReturns\Index as CashReturnsIndex;
use App\Livewire\CashReturns\Upsert as CashReturnsUpsert;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Edit as SettingsEdit;
use App\Livewire\Members\Statement as MembersStatement;
use App\Livewire\Fines\Index as FinesIndex;
use App\Livewire\Fines\Upsert as FinesUpsert;
use App\Livewire\Dividends\Index as DividendsIndex;
use App\Livewire\Portal\Dashboard as PortalDashboard;
use App\Livewire\Portal\ApplyLoan as PortalApplyLoan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public landing page
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasAnyRole(['admin', 'treasurer'])) {
            return redirect()->route('dashboard');
        }
        if ($user->hasRole('member')) {
            return redirect()->route('portal.dashboard');
        }
        return view('landing');
    }
    return view('landing');
})->name('home');

// Guest Routes (Unauthenticated)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Logout Route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Admin panel — accessible to admins and treasurers only
    Route::middleware('role:admin|treasurer')->group(function () {

        // Dashboard Routes
        Route::get('/dashboard', Crm::class)->name('dashboard');
        Route::get('/analytics', Analytics::class)->name('analytics');

        // Members Routes
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', MembersIndex::class)->name('index');
            Route::get('/create', MembersUpsert::class)->name('create');
            Route::get('/{member}/edit', MembersUpsert::class)->name('edit');
        });

        // Contributions Routes
        Route::prefix('contributions')->name('contributions.')->group(function () {
            Route::get('/', ContributionsIndex::class)->name('index');
            Route::get('/create', ContributionsUpsert::class)->name('create');
            Route::get('/{contribution}/edit', ContributionsUpsert::class)->name('edit');
        });

        // Loans Routes
        Route::prefix('loans')->name('loans.')->group(function () {
            Route::get('/', LoansIndex::class)->name('index');
            Route::get('/create', LoansUpsert::class)->name('create');
            Route::get('/{loan}/edit', LoansUpsert::class)->name('edit');
            Route::get('/{loan}', LoansShow::class)->name('show');
        });

        // Expenditures Routes
        Route::prefix('expenditures')->name('expenditures.')->group(function () {
            Route::get('/', ExpendituresIndex::class)->name('index');
            Route::get('/create', ExpendituresUpsert::class)->name('create');
            Route::get('/{expenditure}/edit', ExpendituresUpsert::class)->name('edit');
        });

        // Incomes Routes
        Route::prefix('incomes')->name('incomes.')->group(function () {
            Route::get('/', IncomesIndex::class)->name('index');
            Route::get('/create', IncomesUpsert::class)->name('create');
            Route::get('/{income}/edit', IncomesUpsert::class)->name('edit');
        });

        // Withdrawals Routes
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', WithdrawalsIndex::class)->name('index');
            Route::get('/create', WithdrawalsUpsert::class)->name('create');
            Route::get('/{withdrawal}/edit', WithdrawalsUpsert::class)->name('edit');
        });

        // Cash Returns Routes
        Route::prefix('cash-returns')->name('cash-returns.')->group(function () {
            Route::get('/', CashReturnsIndex::class)->name('index');
            Route::get('/create', CashReturnsUpsert::class)->name('create');
            Route::get('/{cashReturn}/edit', CashReturnsUpsert::class)->name('edit');
        });

        // Reports Route
        Route::get('/reports', ReportsIndex::class)->name('reports');

        // Fines module
        Route::prefix('fines')->name('fines.')->group(function () {
            Route::get('/', FinesIndex::class)->name('index');
            Route::get('/create', FinesUpsert::class)->name('create');
            Route::get('/{fine}/edit', FinesUpsert::class)->name('edit');
        });

        // Dividends / share-out
        Route::get('/dividends', DividendsIndex::class)->name('dividends');
    });

    // Member statement — accessible by staff for any member, and by members
    // for their own record (ownership enforced in the component).
    Route::get('/members/{member}/statement', MembersStatement::class)->name('members.statement');

    // Admin-only — settings & user administration
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', SettingsEdit::class)->name('settings');
    });

    // Member portal (role:member)
    Route::middleware('role:member')->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', PortalDashboard::class)->name('dashboard');
        Route::get('/apply-loan', PortalApplyLoan::class)->name('apply-loan');
        Route::get('/statement', function () {
            $user = Auth::user();
            abort_unless($user && $user->member_id, 403);
            return redirect()->route('members.statement', $user->member_id);
        })->name('statement');
    });
});
