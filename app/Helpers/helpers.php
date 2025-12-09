<?php

if (!function_exists('feature')) {
    /**
     * Check if a feature flag is enabled.
     *
     * @param string $key Feature flag key
     * @param mixed $default Default value if flag doesn't exist
     * @return bool
     */
    function feature(string $key, $default = false): bool
    {
        return \App\Models\FeatureFlag::isEnabled($key) ?? $default;
    }
}

if (!function_exists('feature_config')) {
    /**
     * Get feature flag configuration.
     *
     * @param string $key Feature flag key
     * @param string|null $configKey Specific config key to retrieve
     * @param mixed $default Default value
     * @return mixed
     */
    function feature_config(string $key, ?string $configKey = null, $default = null)
    {
        return \App\Models\FeatureFlag::getConfig($key, $configKey, $default);
    }
}

if (!function_exists('log_system')) {
    /**
     * Log a system message.
     *
     * @param string $level Log level (info, warning, error, critical)
     * @param string $category Log category
     * @param string $message Log message
     * @param array $context Additional context
     * @param \Throwable|null $exception Exception if error/critical
     * @return void
     */
    function log_system(string $level, string $category, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        \App\Models\SystemLog::create([
            'level' => $level,
            'category' => $category,
            'message' => $message,
            'context' => $context,
            'exception' => $exception ? $exception->__toString() : null,
        ]);
    }
}

if (!function_exists('current_department')) {
    /**
     * Get the current user's department.
     *
     * @return \App\Models\Department|null
     */
    function current_department(): ?\App\Models\Department
    {
        if (!auth()->check() || !auth()->user()->department_id) {
            return null;
        }

        return auth()->user()->department;
    }
}

if (!function_exists('can_access_department')) {
    /**
     * Check if current user can access a specific department.
     *
     * @param int $departmentId
     * @return bool
     */
    function can_access_department(int $departmentId): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Admin can access all
        if ($user->hasRole('admin') || $user->hasPermission('browse_all_departments')) {
            return true;
        }

        // Same department
        if ($user->department_id === $departmentId) {
            return true;
        }

        // Department head can access sub-departments
        if ($user->is_department_head && $user->department) {
            $department = \App\Models\Department::with('descendants')->find($user->department_id);
            $allowedIds = [$department->id];

            foreach ($department->descendants as $child) {
                $allowedIds[] = $child->id;
            }

            return in_array($departmentId, $allowedIds);
        }

        return false;
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in bytes to human-readable format.
     *
     * @param int $bytes
     * @return string
     */
    function format_file_size(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize a filename to make it safe for storage.
     *
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Remove special characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);

        // Limit length
        $basename = substr($basename, 0, 200);

        return $basename . ($extension ? '.' . $extension : '');
    }
}

if (!function_exists('generate_idempotency_key')) {
    /**
     * Generate a unique idempotency key.
     *
     * @param string $prefix
     * @return string
     */
    function generate_idempotency_key(string $prefix = ''): string
    {
        return ($prefix ? $prefix . '_' : '') . uniqid() . '_' . md5(microtime(true) . random_bytes(16));
    }
}
