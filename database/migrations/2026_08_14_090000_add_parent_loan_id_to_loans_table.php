<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('parent_loan_id')->nullable()->after('member_id')
                ->comment('The loan whose outstanding balance was re-issued as this one')
                ->constrained('loans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['parent_loan_id']);
            $table->dropColumn('parent_loan_id');
        });
    }
};
