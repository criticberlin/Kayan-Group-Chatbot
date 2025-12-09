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
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 100);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('message_type', 20); // user, assistant, system
            $table->text('message');
            $table->json('context_data')->nullable(); // What data was used to answer
            $table->string('gpt_model', 50)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');

            $table->index('user_id');
            $table->index('session_id');
            $table->index('department_id');
            $table->index('created_at');
            $table->index(['session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
    }
};
