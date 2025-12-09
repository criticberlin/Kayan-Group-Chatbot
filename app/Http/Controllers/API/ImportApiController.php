<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessExcelImport;
use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportApiController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of imports.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Import::departmentScoped()
            ->with(['user:id,name', 'department:id,name'])
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_type')) {
            $query->where('data_type', $request->data_type);
        }

        $imports = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $imports,
        ]);
    }

    /**
     * Store a newly created import.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'data_type' => 'required|in:products,hr_records,faqs,policies',
            'category' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        try {
            $import = $this->importService->upload($request->file('file'), [
                'data_type' => $request->data_type,
                'category' => $request->category,
                'department_id' => $request->department_id,
            ]);

            ProcessExcelImport::dispatch($import);

            return response()->json([
                'success' => true,
                'message' => 'Import started successfully',
                'data' => [
                    'import_id' => $import->id,
                    'status' => $import->status,
                    'file_name' => $import->file_name,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified import.
     */
    public function show(Import $import): JsonResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $import->load('user:id,name', 'department:id,name');

        return response()->json([
            'success' => true,
            'data' => $import,
        ]);
    }

    /**
     * Get import progress.
     */
    public function progress(Import $import): JsonResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $progress = $this->importService->getProgress($import);

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Reprocess failed import.
     */
    public function reprocess(Import $import): JsonResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        if (!in_array($import->status, ['failed', 'completed_with_errors'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only failed imports can be reprocessed',
            ], 400);
        }

        try {
            $import->rows()->failed()->update(['status' => 'pending']);

            $import->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            ProcessExcelImport::dispatch($import);

            return response()->json([
                'success' => true,
                'message' => 'Import reprocessing started',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reprocess failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified import.
     */
    public function destroy(Import $import): JsonResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        try {
            $this->importService->deleteImport($import);

            return response()->json([
                'success' => true,
                'message' => 'Import deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get failed rows.
     */
    public function failedRows(Import $import): JsonResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
            ], 403);
        }

        $failedRows = $import->rows()
            ->failed()
            ->select('id', 'row_number', 'raw_data', 'validation_errors')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $failedRows,
        ]);
    }
}
