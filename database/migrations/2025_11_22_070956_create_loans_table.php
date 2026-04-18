<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_percent', 5, 2)->default(10);
            $table->integer('term_months')->default(6);
            $table->date('disbursed_at')->nullable();
            $table->date('due_at')->nullable();
            $table->decimal('repaid_amount', 12, 2)->default(0);
            $table->boolean('repaid')->default(false);
            $table->string('status')->default('pending'); 
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('loans');
    }
};