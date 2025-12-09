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
        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('import_id');
            $table->unsignedInteger('row_number');
            $table->json('raw_data');
            $table->json('parsed_data')->nullable();
            $table->string('status', 20)->default('pending'); // pending, validated, imported, failed
            $table->string('target_table', 50)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('validation_errors')->nullable();
            $table->timestamps();

            $table->foreign('import_id')->references('id')->on('imports')->onDelete('cascade');

            $table->index('import_id');
            $table->index('status');
            $table->index(['target_table', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
