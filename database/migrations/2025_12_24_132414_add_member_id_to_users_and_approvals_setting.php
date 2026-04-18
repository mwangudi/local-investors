<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('chama_settings', function (Blueprint $table) {
            $table->integer('min_loan_approvals')->default(3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn('member_id');
        });

        Schema::table('chama_settings', function (Blueprint $table) {
            $table->dropColumn('min_loan_approvals');
        });
    }
};
