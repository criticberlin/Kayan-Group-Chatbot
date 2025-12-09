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
        Schema::create('chat_contexts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('source_table', 50); // products, faqs, policies, etc.
            $table->unsignedBigInteger('source_id');
            $table->text('context_text');
            $table->text('keywords')->nullable();
            $table->unsignedInteger('relevance_score')->default(0);
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

            $table->index('department_id');
            $table->index(['source_table', 'source_id']);
            $table->index('relevance_score');
            $table->fullText(['context_text', 'keywords']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_contexts');
    }
};
