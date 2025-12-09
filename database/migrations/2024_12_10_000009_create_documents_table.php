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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->string('document_type', 50)->nullable(); // pdf, word, excel, image
            $table->string('category', 100)->nullable();
            $table->longText('extracted_text')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable(); // For versioning
            $table->boolean('is_latest')->default(true);
            $table->string('access_level', 20)->default('department');
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('documents')->onDelete('set null');

            $table->index('department_id');
            $table->index('user_id');
            $table->index('category');
            $table->index('document_type');
            $table->index(['parent_id', 'version']);
            $table->index('is_latest');
            $table->fullText(['title', 'description', 'extracted_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
