<?php

namespace App\Jobs;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportValidatedRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

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
    public function handle(): void
    {
        try {
            SystemLog::info('import', "Importing validated rows: Import {$this->import->id}");

            $targetModel = $this->getTargetModel($this->import->data_type);

            if (!$targetModel) {
                throw new \Exception("Unknown data type: {$this->import->data_type}");
            }

            $validatedRows = ImportRow::where('import_id', $this->import->id)
                ->where('status', 'validated')
                ->get();

            $importedCount = 0;
            $failedCount = 0;

            foreach ($validatedRows as $row) {
                DB::beginTransaction();

                try {
                    $data = $row->parsed_data ?? $row->raw_data;
                    $data['import_id'] = $this->import->id;
                    $data['department_id'] = $this->import->department_id;
                    $data['created_by'] = $this->import->user_id;

                    $record = $targetModel::create($data);

                    $row->markAsImported(
                        (new $targetModel)->getTable(),
                        $record->id
                    );

                    DB::commit();
                    $importedCount++;

                } catch (\Exception $e) {
                    DB::rollBack();

                    $row->markAsFailed(['Import error: ' . $e->getMessage()]);
                    $failedCount++;

                    SystemLog::error('import', "Row import failed: Import {$this->import->id}, Row {$row->row_number}", $e);
                }
            }

            $this->import->update([
                'processed_rows' => $importedCount,
                'failed_rows' => $failedCount,
                'status' => $failedCount > 0 ? 'completed_with_errors' : 'completed',
                'completed_at' => now(),
            ]);

            SystemLog::info('import', "Import complete: Import {$this->import->id} - Success: {$importedCount}, Failed: {$failedCount}");

        } catch (\Exception $e) {
            SystemLog::error('import', "Import job failed: Import {$this->import->id}", $e);

            $this->import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get target model class from data type.
     */
    protected function getTargetModel(string $dataType): ?string
    {
        $mapping = [
            'products' => \App\Models\Product::class,
            'hr_records' => \App\Models\HrRecord::class,
            'faqs' => \App\Models\FaqRecord::class,
            'policies' => \App\Models\PolicyDocument::class,
        ];

        return $mapping[$dataType] ?? null;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->import->update([
            'status' => 'failed',
            'error_message' => "Import job failed: " . $exception->getMessage(),
        ]);

        SystemLog::critical('import', "Import job failed permanently: Import {$this->import->id}", $exception);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'import',
            'import:' . $this->import->id,
            'data_type:' . $this->import->data_type,
        ];
    }
}
