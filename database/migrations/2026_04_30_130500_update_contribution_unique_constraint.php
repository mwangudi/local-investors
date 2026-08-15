<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('contributions', function (Blueprint $table) {
            // Add new constraint based on contribution period and type FIRST
            // This ensures member_id remains indexed for the foreign key
            $table->unique(['member_id', 'contribution_period', 'type'], 'unique_contribution_period');

            // Drop old constraint
            $table->dropUnique('unique_monthly_contribution');
        });
    }

    public function down()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropUnique('unique_contribution_period');
            $table->unique(['member_id', 'paid_at'], 'unique_monthly_contribution');
        });
    }
};
