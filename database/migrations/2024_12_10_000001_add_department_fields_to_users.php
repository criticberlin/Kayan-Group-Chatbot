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
        Schema::table('users', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
            }

            if (!Schema::hasColumn('users', 'is_department_head')) {
                $table->boolean('is_department_head')->default(false)->after('department_id');
            }

            if (!Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->unique()->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('updated_at');
            }

            // Voyager already has a settings column, so skip it
            if (!Schema::hasColumn('users', 'settings')) {
                $table->json('settings')->nullable()->after('last_login_at');
            }
        });

        // Add index for department_id if it doesn't exist
        if (!$this->indexExists('users', 'users_department_id_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('department_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop index first
            if ($this->indexExists('users', 'users_department_id_index')) {
                $table->dropIndex(['department_id']);
            }

            // Drop columns if they exist
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'department_id')) {
                $columnsToDrop[] = 'department_id';
            }
            if (Schema::hasColumn('users', 'is_department_head')) {
                $columnsToDrop[] = 'is_department_head';
            }
            if (Schema::hasColumn('users', 'api_token')) {
                $columnsToDrop[] = 'api_token';
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $columnsToDrop[] = 'last_login_at';
            }
            // Note: We don't drop settings as it may be owned by Voyager

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Check if an index exists.
     */
    protected function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->introspectTable($table);

        return $doctrineTable->hasIndex($index);
    }
};
