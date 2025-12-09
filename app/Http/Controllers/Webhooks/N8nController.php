<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ImportValidatedRows;
use App\Models\Import;
use App\Models\SystemLog;
use App\Services\ImportService;
use App\Services\N8nService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class N8nController extends Controller
{
    protected N8nService $n8nService;
    protected ImportService $importService;

    public function __construct(N8nService $n8nService, ImportService $importService)
    {
        $this->n8nService = $n8nService;
        $this->importService = $importService;
    }

    /**
     * Handle n8n webhook callback.
     */
    public function callback(Request $request): JsonResponse
    {
        // Verify signature
        $signature = $request->header('X-N8N-Signature');
        $payload = $request->getContent();

        if (!$signature || !$this->n8nService->verifyCallbackSignature($payload, $signature)) {
            SystemLog::warning('n8n', 'Invalid webhook signature', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        // Get data
        $data = $request->all();

        try {
            // Validate required fields
            $request->validate([
                'import_id' => 'required|exists:imports,id',
                'status' => 'required|in:success,failed',
                'validated_rows' => 'sometimes|array',
            ]);

            $import = Import::findOrFail($data['import_id']);

            SystemLog::info('n8n', "Webhook callback received for import {$import->id}", [
                'status' => $data['status'],
                'rows_count' => count($data['validated_rows'] ?? []),
            ]);

            // Handle response
            if ($data['status'] === 'success') {
                $this->importService->handleN8nResponse($import, $data);

                // Optionally chain to import job
                if (feature('auto_import_validated_rows', true)) {
                    ImportValidatedRows::dispatch($import);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Callback processed successfully',
                ]);
            } else {
                // Handle failure
                $import->update([
                    'status' => 'failed',
                    'error_message' => $data['error_message'] ?? 'n8n validation failed',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Failure acknowledged',
                ]);
            }

        } catch (\Exception $e) {
            SystemLog::error('n8n', 'Webhook callback failed', $e, [
                'import_id' => $data['import_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Health check endpoint for n8n.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is healthy',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle custom webhook from n8n.
     */
    public function custom(Request $request, string $action): JsonResponse
    {
        // Verify signature
        $signature = $request->header('X-N8N-Signature');
        $payload = $request->getContent();

        if (!$signature || !$this->n8nService->verifyCallbackSignature($payload, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        try {
            SystemLog::info('n8n', "Custom webhook received: {$action}", $request->all());

            // Handle different actions
            $result = match ($action) {
                'test' => ['message' => 'Test successful'],
                'ping' => ['message' => 'Pong'],
                default => throw new \Exception("Unknown action: {$action}"),
            };

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            SystemLog::error('n8n', "Custom webhook failed: {$action}", $e);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
