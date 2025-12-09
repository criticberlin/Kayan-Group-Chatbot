<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessExcelImport;
use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;

        $this->middleware('auth');




    }

    /**
     * Display a listing of imports.
     */
    public function index(Request $request): View
    {
        $query = Import::with(['user', 'department'])
            ->departmentScoped()
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_type')) {
            $query->where('data_type', $request->data_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $imports = $query->paginate(20);

        return view('admin.imports.index', compact('imports'));
    }

    /**
     * Show the form for creating a new import.
     */
    public function create(): View
    {
        $dataTypes = [
            'products' => 'Products',
            'hr_records' => 'HR Records',
            'faqs' => 'FAQs',
            'policies' => 'Policy Documents',
        ];

        return view('admin.imports.create', compact('dataTypes'));
    }

    /**
     * Store a newly created import.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
            'data_type' => 'required|in:products,hr_records,faqs,policies',
            'category' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        try {
            // Upload file and create import
            $import = $this->importService->upload($request->file('file'), [
                'data_type' => $request->data_type,
                'category' => $request->category,
                'department_id' => $request->department_id,
            ]);

            // Dispatch background job
            ProcessExcelImport::dispatch($import);

            return redirect()
                ->route('admin.imports.show', $import)
                ->with('success', 'Import started successfully. Processing in background...');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified import.
     */
    public function show(Import $import): View
    {
        // Check department access
        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403, 'You do not have permission to view this import');
        }

        $import->load([
            'user',
            'department',
            'rows' => function ($query) {
                $query->latest()->limit(100);
            }
        ]);

        $progress = $this->importService->getProgress($import);

        $failedRows = $import->rows()->failed()->get();

        return view('admin.imports.show', compact('import', 'progress', 'failedRows'));
    }

    /**
     * Reprocess failed import.
     */
    public function reprocess(Import $import): RedirectResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403);
        }

        if (!in_array($import->status, ['failed', 'completed_with_errors'])) {
            return back()->with('error', 'Only failed imports can be reprocessed');
        }

        try {
            // Reset failed rows
            $import->rows()->failed()->update(['status' => 'pending']);

            $import->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            // Dispatch job again
            ProcessExcelImport::dispatch($import);

            return back()->with('success', 'Import reprocessing started');

        } catch (\Exception $e) {
            return back()->with('error', 'Reprocess failed: ' . $e->getMessage());
        }
    }

    /**
     * Rollback import.
     */
    public function rollback(Import $import): RedirectResponse
    {
        // Simple admin check - TODO: Implement proper permission system
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403);
        }

        try {
            $this->importService->rollbackImport($import);

            return back()->with('success', 'Import rolled back successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified import.
     */
    public function destroy(Import $import): RedirectResponse
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403);
        }

        try {
            $this->importService->deleteImport($import);

            return redirect()
                ->route('admin.imports.index')
                ->with('success', 'Import deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Download import file.
     */
    public function download(Import $import)
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403);
        }

        return \Storage::disk('imports')->download($import->file_path, $import->file_name);
    }

    /**
     * Export failed rows as CSV.
     */
    public function exportErrors(Import $import)
    {
        if (!$import->canBeAccessedByUser(auth()->user())) {
            abort(403);
        }

        $failedRows = $import->rows()->failed()->get();

        $csv = "Row Number,Errors,Data\n";

        foreach ($failedRows as $row) {
            $errors = implode('; ', $row->validation_errors ?? []);
            $data = json_encode($row->raw_data);

            $csv .= "{$row->row_number},\"{$errors}\",\"{$data}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=import-{$import->id}-errors.csv");
    }
}
