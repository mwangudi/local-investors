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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('notifiable');                   // notifiable_id + notifiable_type
            $table->string('channel');                      // 'sms' or 'email'
            $table->string('notification_type');            // e.g. LoanStatusChanged
            $table->string('recipient');                    // phone or email address
            $table->text('content');                        // message body
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);        // For retry queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
