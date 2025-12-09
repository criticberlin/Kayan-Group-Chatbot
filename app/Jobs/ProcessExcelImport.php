<?php

namespace App\Jobs;

use App\Models\Import;
use App\Services\ImportService;
use App\Models\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExcelImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes

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
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(ImportService $importService): void
    {
        try {
            SystemLog::info('import', "Starting Excel processing: Import {$this->import->id}");

            // Process Excel file
            $importService->processExcel($this->import);

            // Chain to n8n sending job
            SendToN8n::dispatch($this->import->fresh());

            SystemLog::info('import', "Excel processing complete: Import {$this->import->id}");

        } catch (\Exception $e) {
            SystemLog::error('import', "Excel processing failed: Import {$this->import->id}", $e);

            $this->import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

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
            'error_message' => "Job failed after {$this->tries} attempts: " . $exception->getMessage(),
        ]);

        SystemLog::critical('import', "Excel processing job failed permanently: Import {$this->import->id}", $exception);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'import',
            'import:' . $this->import->id,
            'user:' . $this->import->user_id,
            'department:' . $this->import->department_id,
        ];
    }
}
