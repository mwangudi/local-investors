<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshFinancials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-financials {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all contributions, loans, and related financial records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to clear all financial records? This cannot be undone.')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Clearing financial records...');

        Schema::disableForeignKeyConstraints();

        DB::table('contributions')->truncate();
        DB::table('loans')->truncate();
        DB::table('loan_repayments')->truncate();
        DB::table('loan_approvals')->truncate();
        DB::table('incomes')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->info('All financial records have been cleared.');
    }
}
