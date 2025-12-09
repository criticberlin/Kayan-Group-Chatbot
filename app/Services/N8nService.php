<?php

namespace App\Services;

use App\Models\Import;
use App\Models\N8nJob;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Http;

class N8nService
{
    protected ?string $webhookUrl;
    protected ?string $callbackSecret;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->webhookUrl = config('services.n8n.webhook_url');
        $this->callbackSecret = config('services.n8n.callback_secret');
        $this->timeout = config('services.n8n.timeout', 30);
        $this->maxRetries = config('services.n8n.max_retries', 3);
    }

    /**
     * Send import data to n8n webhook.
     */
    public function sendImportData(Import $import): N8nJob
    {
        $payload = $this->prepareImportPayload($import);
        $idempotencyKey = generate_idempotency_key("import_{$import->id}");

        // Check if job already exists with this idempotency key
        $existingJob = N8nJob::where('idempotency_key', $idempotencyKey)->first();
        if ($existingJob && !$existingJob->hasFailed()) {
            SystemLog::info('n8n', "Job already exists with idempotency key: {$idempotencyKey}");
            return $existingJob;
        }

        // Create n8n job record
        $job = N8nJob::create([
            'import_id' => $import->id,
            'webhook_url' => $this->webhookUrl,
            'request_payload' => $payload,
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
        ]);

        // Send to n8n
        try {
            $job->markAsProcessing();

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey,
                ])
                ->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                $job->markAsCompleted($response->json());
                $import->update(['n8n_job_id' => $job->id]);

                SystemLog::info('n8n', "Import data sent successfully: Import {$import->id}, Job {$job->id}");
            } else {
                throw new \Exception("n8n webhook returned {$response->status()}: {$response->body()}");
            }

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            SystemLog::error('n8n', "Failed to send import data: Import {$import->id}, Job {$job->id}", $e);

            // Will be retried by background job
            throw $e;
        }

        return $job;
    }

    /**
     * Prepare import payload for n8n.
     */
    protected function prepareImportPayload(Import $import): array
    {
        // Load rows in chunks to avoid memory issues
        $rows = $import->rows()
            ->pending()
            ->limit(config('services.excel.chunk_size', 1000))
            ->get()
            ->map(function ($row) {
                return [
                    'row_number' => $row->row_number,
                    'data' => $row->raw_data,
                ];
            })
            ->toArray();

        return [
            'import_id' => $import->id,
            'data_type' => $import->data_type,
            'category' => $import->category,
            'department_id' => $import->department_id,
            'callback_url' => url('/api/webhooks/n8n/callback'),
            'rows' => $rows,
            'total_rows' => $import->total_rows,
        ];
    }

    /**
     * Verify webhook callback signature.
     */
    public function verifyCallbackSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->callbackSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Retry failed n8n job.
     */
    public function retryJob(N8nJob $job): void
    {
        if (!$job->canRetry($this->maxRetries)) {
            throw new \Exception("Job {$job->id} has exceeded maximum retries");
        }

        try {
            $job->incrementRetry();
            $job->update(['status' => 'processing']);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $job->idempotency_key,
                ])
                ->post($job->webhook_url, $job->request_payload);

            if ($response->successful()) {
                $job->markAsCompleted($response->json());
                SystemLog::info('n8n', "Job retry successful: {$job->id}");
            } else {
                throw new \Exception("n8n webhook returned {$response->status()}: {$response->body()}");
            }

        } catch (\Exception $e) {
            $job->markAsFailed($e->getMessage());
            SystemLog::error('n8n', "Job retry failed: {$job->id}", $e);
            throw $e;
        }
    }

    /**
     * Send custom webhook request to n8n.
     */
    public function sendCustomWebhook(string $endpoint, array $data, ?string $idempotencyKey = null): array
    {
        $url = rtrim($this->webhookUrl, '/') . '/' . ltrim($endpoint, '/');
        $idempotencyKey = $idempotencyKey ?? generate_idempotency_key();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey,
                ])
                ->post($url, $data);

            if (!$response->successful()) {
                throw new \Exception("n8n webhook returned {$response->status()}: {$response->body()}");
            }

            SystemLog::info('n8n', "Custom webhook sent: {$endpoint}");

            return $response->json();

        } catch (\Exception $e) {
            SystemLog::error('n8n', "Custom webhook failed: {$endpoint}", $e);
            throw $e;
        }
    }

    /**
     * Get webhook status from n8n (if supported).
     */
    public function getWebhookStatus(string $jobId): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->webhookUrl . "/status/{$jobId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            SystemLog::warning('n8n', "Failed to get webhook status: {$jobId}");
            return null;
        }
    }

    /**
     * Check if n8n is available.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->webhookUrl . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
