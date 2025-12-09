@extends('voyager::master')

@section('page_title', 'Imports')

@section('page_header')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-upload"></i> Excel Imports
    </h1>

    <a href="{{ route('admin.imports.create') }}" class="btn btn-success btn-add-new">
        <i class="voyager-plus"></i> <span>Upload New Import</span>
    </a>

</div>
@stop

@section('content')
<div class="page-content browse container-fluid">
    @include('voyager::alerts')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.imports.index') }}" class="form-inline"
                        style="margin-bottom: 20px;">
                        <div class="form-group" style="margin-right: 10px;">
                            <label for="status" style="margin-right: 5px;">Status:</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                    Processing</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed
                                </option>
                                <option value="completed_with_errors" {{ request('status') == 'completed_with_errors' ? 'selected' : '' }}>Completed with Errors</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-right: 10px;">
                            <label for="data_type" style="margin-right: 5px;">Type:</label>
                            <select name="data_type" id="data_type" class="form-control">
                                <option value="">All</option>
                                <option value="products" {{ request('data_type') == 'products' ? 'selected' : '' }}>
                                    Products</option>
                                <option value="hr_records" {{ request('data_type') == 'hr_records' ? 'selected' : '' }}>HR
                                    Records</option>
                                <option value="faqs" {{ request('data_type') == 'faqs' ? 'selected' : '' }}>FAQs</option>
                                <option value="policies" {{ request('data_type') == 'policies' ? 'selected' : '' }}>
                                    Policies</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="voyager-search"></i> Filter
                        </button>

                        @if(request()->anyFilled(['status', 'data_type', 'date_from', 'date_to']))
                            <a href="{{ route('admin.imports.index') }}" class="btn btn-default">
                                <i class="voyager-x"></i> Clear
                            </a>
                        @endif
                    </form>

                    <!-- Imports Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>User</th>
                                    <th>Department</th>
                                    <th>Created</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($imports as $import)
                                    <tr>
                                        <td>{{ $import->id }}</td>
                                        <td>
                                            <i class="voyager-file-text"></i>
                                            {{ $import->file_name }}
                                            <br>
                                            <small class="text-muted">{{ format_file_size($import->file_size) }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="label label-default">{{ ucfirst(str_replace('_', ' ', $import->data_type)) }}</span>
                                        </td>
                                        <td>
                                            @if($import->status == 'completed')
                                                <span class="label label-success">Completed</span>
                                            @elseif($import->status == 'processing')
                                                <span class="label label-info">Processing</span>
                                            @elseif($import->status == 'failed')
                                                <span class="label label-danger">Failed</span>
                                            @elseif($import->status == 'completed_with_errors')
                                                <span class="label label-warning">With Errors</span>
                                            @else
                                                <span class="label label-default">{{ ucfirst($import->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($import->total_rows > 0)
                                                <div class="progress" style="margin-bottom: 0;">
                                                    <div class="progress-bar progress-bar-{{ $import->status == 'completed' ? 'success' : ($import->status == 'failed' ? 'danger' : 'info') }}"
                                                        role="progressbar" style="width: {{ $import->progress_percentage }}%">
                                                        {{ $import->progress_percentage }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $import->processed_rows }}/{{ $import->total_rows }} rows
                                                </small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>{{ $import->user->name ?? 'N/A' }}</td>
                                        <td>{{ $import->department->name ?? 'N/A' }}</td>
                                        <td>
                                            {{ $import->created_at->format('M d, Y') }}
                                            <br>
                                            <small class="text-muted">{{ $import->created_at->format('H:i') }}</small>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.imports.show', $import) }}" class="btn btn-sm btn-info"
                                                title="View Details">
                                                <i class="voyager-eye"></i>
                                            </a>

                                            @if(in_array($import->status, ['failed', 'completed_with_errors']))
                                                <form action="{{ route('admin.imports.reprocess', $import) }}" method="POST"
                                                    style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Reprocess"
                                                        onclick="return confirm('Reprocess this import?')">
                                                        <i class="voyager-refresh"></i>
                                                    </button>
                                                </form>
                                            @endif


                                            <form action="{{ route('admin.imports.destroy', $import) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                    onclick="return confirm('Delete this import?')">
                                                    <i class="voyager-trash"></i>
                                                </button>
                                            </form>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <p style="padding: 40px 0;">
                                                <i class="voyager-upload" style="font-size: 48px; color: #ccc;"></i>
                                                <br><br>
                                                No imports found.

                                                <a href="{{ route('admin.imports.create') }}">Upload your first import</a>

                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pull-right">
                        {{ $imports->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .progress {
        height: 18px;
        margin-bottom: 5px;
    }

    .label {
        font-size: 11px;
        padding: 4px 8px;
    }
</style>
@stop