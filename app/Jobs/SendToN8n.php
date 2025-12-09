<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\SystemLog;
use App\Services\N8nService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendToN8n implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * The import instance.
     */
    protected Import $import;

    /**
     * Create a new job instance.
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
        $this->onQueue('n8n');
    }

    /**
     * Execute the job.
     */
    public function handle(N8nService $n8nService): void
    {
        // Check if n8n integration is enabled
        if (!feature('n8n_integration', true)) {
            SystemLog::warning('n8n', "n8n integration disabled, skipping import {$this->import->id}");

            // Mark import as completed without n8n validation
            $this->import->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return;
        }

        try {
            SystemLog::info('n8n', "Sending import to n8n: Import {$this->import->id}");

            // Send to n8n
            $n8nService->sendImportData($this->import);

            SystemLog::info('n8n', "Successfully sent import to n8n: Import {$this->import->id}");

        } catch (\Exception $e) {
            SystemLog::error('n8n', "Failed to send import to n8n: Import {$this->import->id}", $e);

            // Don't mark import as failed immediately, will retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->import->update([
            'status' => 'failed',
            'error_message' => "n8n sending failed after {$this->tries} attempts: " . $exception->getMessage(),
        ]);

        SystemLog::critical('n8n', "n8n sending job failed permanently: Import {$this->import->id}", $exception);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'n8n',
            'import:' . $this->import->id,
            'user:' . $this->import->user_id,
        ];
    }
}
