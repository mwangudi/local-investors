<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('shares', 10, 2)->default(0)->nullable();
            $table->decimal('welfare', 10, 2)->default(0)->nullable();
            $table->decimal('penalty', 10, 2)->default(0)
                ->comment('Late payment or other penalties')->nullable();
            $table->string('penalty_type')->nullable();
            $table->string('type')->default('monthly')->comment('monthly, welfare, penalty, special');
            $table->decimal('merry_go_round', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            
            $table->softDeletes();
            $table->timestamps();

            $table->unique(
                ['member_id', 'paid_at'],
                'unique_monthly_contribution'
            );
        });
    }
    public function down()
    {
        Schema::dropIfExists('contributions');
    }
};