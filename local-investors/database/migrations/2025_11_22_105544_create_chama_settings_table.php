<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chama_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('standard_interest_percent', 5, 2)->default(10); 
            $table->decimal('overdue_penalty_percent', 5, 2)->default(30);  
            $table->integer('loan_duration_months')->default(2);           
            $table->integer('grace_period_days')->default(0);               
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('chama_settings')->insert([
            'standard_interest_percent' => 10,
            'overdue_penalty_percent' => 30,
            'loan_duration_months' => 2,
            'grace_period_days' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chama_settings');
    }
};
