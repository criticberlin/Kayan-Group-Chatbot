<?php

namespace App\Services;

use App\Models\Import;
use App\Models\ImportRow;
use App\Models\SystemLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    /**
     * Upload and initialize an import.
     */
    public function upload(UploadedFile $file, array $options): Import
    {
        // Validate file
        $this->validateFile($file);

        // Store file
        $userId = auth()->id();
        $departmentId = $options['department_id'] ?? auth()->user()->department_id;
        $path = $file->store("imports/{$userId}/" . date('Y/m'), 'imports');

        // Create import record
        $import = Import::create([
            'user_id' => $userId,
            'department_id' => $departmentId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'data_type' => $options['data_type'],
            'category' => $options['category'] ?? null,
            'status' => 'pending',
        ]);

        SystemLog::info('import', "Import created: {$import->id} - {$file->getClientOriginalName()}");

        return $import;
    }

    /**
     * Validate uploaded file.
     */
    public function validateFile(UploadedFile $file): bool
    {
        $maxSize = config('services.excel.max_file_size', 10485760);
        $allowedExtensions = config('services.excel.allowed_extensions', ['xlsx', 'xls', 'csv']);

        // Check file size
        if ($file->getSize() > $maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . format_file_size($maxSize));
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("File type '{$extension}' is not allowed. Allowed types: " . implode(', ', $allowedExtensions));
        }

        return true;
    }

    /**
     * Process Excel file and create import rows.
     */
    public function processExcel(Import $import): void
    {
        try {
            $import->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $filePath = Storage::disk('imports')->path($import->file_path);
            $parser = new ExcelParserService();
            $rowCount = 0;

            DB::beginTransaction();

            // Stream rows from Excel file
            foreach ($parser->streamRows($filePath) as $index => $row) {
                // Skip header row
                if ($index === 0) {
                    continue;
                }

                ImportRow::create([
                    'import_id' => $import->id,
                    'row_number' => $index,
                    'raw_data' => $row,
                    'status' => 'pending',
                ]);

                $rowCount++;

                // Commit in batches to avoid memory issues
                if ($rowCount % 1000 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }

            $import->update(['total_rows' => $rowCount]);

            DB::commit();

            SystemLog::info('import', "Excel processed: {$import->id} - {$rowCount} rows");

        } catch (\Exception $e) {
            DB::rollBack();

            $import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            SystemLog::error('import', "Excel processing failed: {$import->id}", $e);

            throw $e;
        }
    }

    /**
     * Send import to n8n for validation.
     */
    public function sendToN8n(Import $import): void
    {
        if (!feature('n8n_integration', true)) {
            SystemLog::warning('import', "n8n integration disabled, skipping import {$import->id}");
            return;
        }

        $n8nService = new N8nService();
        $n8nService->sendImportData($import);
    }

    /**
     * Handle n8n callback response.
     */
    public function handleN8nResponse(Import $import, array $data): void
    {
        try {
            DB::beginTransaction();

            $validatedRows = $data['validated_rows'] ?? [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($validatedRows as $rowData) {
                $row = ImportRow::where('import_id', $import->id)
                    ->where('row_number', $rowData['row_number'])
                    ->first();

                if (!$row) {
                    continue;
                }

                if ($rowData['valid']) {
                    $row->markAsValidated($rowData['data'] ?? null);
                    $successCount++;
                } else {
                    $row->markAsFailed($rowData['errors'] ?? ['Validation failed']);
                    $failedCount++;
                }
            }

            $import->update([
                'processed_rows' => $successCount,
                'failed_rows' => $failedCount,
                'status' => $failedCount > 0 ? 'completed_with_errors' : 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            SystemLog::info('import', "n8n response processed: {$import->id} - Success: {$successCount}, Failed: {$failedCount}");

        } catch (\Exception $e) {
            DB::rollBack();

            $import->update([
                'status' => 'failed',
                'error_message' => 'Failed to process n8n response: ' . $e->getMessage(),
            ]);

            SystemLog::error('import', "n8n response processing failed: {$import->id}", $e);

            throw $e;
        }
    }

    /**
     * Import validated rows into target table.
     */
    public function importValidatedRows(Import $import): void
    {
        $validatedRows = ImportRow::where('import_id', $import->id)
            ->validated()
            ->get();

        $targetModel = $this->getTargetModel($import->data_type);

        if (!$targetModel) {
            throw new \Exception("Unknown data type: {$import->data_type}");
        }

        $importedCount = 0;

        foreach ($validatedRows as $row) {
            try {
                $data = $row->parsed_data ?? $row->raw_data;
                $data['import_id'] = $import->id;
                $data['department_id'] = $import->department_id;

                $record = $targetModel::create($data);

                $row->markAsImported(
                    (new $targetModel)->getTable(),
                    $record->id
                );

                $importedCount++;

            } catch (\Exception $e) {
                $row->markAsFailed(['Import error: ' . $e->getMessage()]);
                SystemLog::error('import', "Row import failed: Import {$import->id}, Row {$row->row_number}", $e);
            }
        }

        $import->increment('processed_rows', $importedCount);

        SystemLog::info('import', "Rows imported: {$import->id} - {$importedCount} records");
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
     * Rollback import (delete all imported records).
     */
    public function rollbackImport(Import $import): void
    {
        $rows = ImportRow::where('import_id', $import->id)
            ->imported()
            ->get();

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $modelClass = $this->getTargetModel($import->data_type);
                if ($modelClass && $row->target_id) {
                    $modelClass::where('id', $row->target_id)->delete();
                }

                $row->update(['status' => 'pending', 'target_id' => null, 'target_table' => null]);
            }

            $import->update([
                'status' => 'pending',
                'processed_rows' => 0,
            ]);

            DB::commit();

            SystemLog::warning('import', "Import rolled back: {$import->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get import progress data.
     */
    public function getProgress(Import $import): array
    {
        return [
            'total' => $import->total_rows,
            'processed' => $import->processed_rows,
            'failed' => $import->failed_rows,
            'percentage' => $import->progress_percentage,
            'status' => $import->status,
            'started_at' => $import->started_at?->toDateTimeString(),
            'completed_at' => $import->completed_at?->toDateTimeString(),
        ];
    }

    /**
     * Delete import and its file.
     */
    public function deleteImport(Import $import): void
    {
        // Delete file
        if (Storage::disk('imports')->exists($import->file_path)) {
            Storage::disk('imports')->delete($import->file_path);
        }

        // Delete import (cascade will delete rows)
        $import->delete();

        SystemLog::info('import', "Import deleted: {$import->id}");
    }
}
