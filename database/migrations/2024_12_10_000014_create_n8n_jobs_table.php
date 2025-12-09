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
        Schema::create('n8n_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id')->nullable();
            $table->string('webhook_url', 500);
            $table->json('request_payload');
            $table->json('response_payload')->nullable();
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->string('idempotency_key', 100)->unique()->nullable();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();

            $table->foreign('import_id')->references('id')->on('imports')->onDelete('set null');

            $table->index('import_id');
            $table->index('status');
            $table->index('idempotency_key');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_jobs');
    }
};
