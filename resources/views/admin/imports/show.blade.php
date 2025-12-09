@extends('voyager::master')

@section('page_title', 'Import Details')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-file-text"></i> Import #{{ $import->id }} - {{ $import->file_name }}
        </h1>
        <a href="{{ route('admin.imports.index') }}" class="btn btn-default">
            <i class="voyager-list"></i> Back to List
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')

        <div class="row">
            <!-- Status Card -->
            <div class="col-md-8">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-info-circled"></i> Import Status
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="dl-horizontal">
                                    <dt>Status:</dt>
                                    <dd>
                                        @if($import->status == 'completed')
                                            <span class="label label-success">Completed</span>
                                        @elseif($import->status == 'processing')
                                            <span class="label label-info">Processing</span>
                                        @elseif($import->status == 'failed')
                                            <span class="label label-danger">Failed</span>
                                        @elseif($import->status == 'completed_with_errors')
                                            <span class="label label-warning">Completed with Errors</span>
                                        @else
                                            <span class="label label-default">{{ ucfirst($import->status) }}</span>
                                        @endif
                                    </dd>
                                    
                                    <dt>Data Type:</dt>
                                    <dd>{{ ucfirst(str_replace('_', ' ', $import->data_type)) }}</dd>
                                    
                                    @if($import->category)
                                        <dt>Category:</dt>
                                        <dd>{{ $import->category }}</dd>
                                    @endif

                                    <dt>File Size:</dt>
                                    <dd>{{ format_file_size($import->file_size) }}</dd>
                                </dl>
                            </div>
                            
                            <div class="col-md-6">
                                <dl class="dl-horizontal">
                                    <dt>Uploaded By:</dt>
                                    <dd>{{ $import->user->name }}</dd>
                                    
                                    <dt>Department:</dt>
                                    <dd>{{ $import->department->name ?? 'N/A' }}</dd>
                                    
                                    <dt>Created:</dt>
                                    <dd>{{ $import->created_at->format('M d, Y H:i') }}</dd>
                                    
                                    @if($import->completed_at)
                                        <dt>Completed:</dt>
                                        <dd>{{ $import->completed_at->format('M d, Y H:i') }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        @if($import->error_message)
                            <div class="alert alert-danger" style="margin-top: 15px;">
                                <strong>Error:</strong> {{ $import->error_message }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-bar-chart"></i> Progress
                        </h3>
                    </div>
                    <div class="panel-body">
                        @if($import->total_rows > 0)
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar progress-bar-{{ $import->status == 'completed' ? 'success' : ($import->status == 'failed' ? 'danger' : 'info') }}" 
                                     role="progressbar" 
                                     style="width: {{ $progress['percentage'] }}%; line-height: 30px; font-size: 14px;">
                                    {{ $progress['percentage'] }}%
                                </div>
                            </div>
                            
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-4 text-center">
                                    <h4>{{ number_format($import->total_rows) }}</h4>
                                    <p class="text-muted">Total Rows</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h4 class="text-success">{{ number_format($import->processed_rows) }}</h4>
                                    <p class="text-muted">Processed</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h4 class="text-danger">{{ number_format($import->failed_rows) }}</h4>
                                    <p class="text-muted">Failed</p>
                                </div>
                            </div>
                        @else
                            <p class="text-center text-muted" style="padding: 20px 0;">
                                Processing has not started yet...
                            </p>
                        @endif

                        @if($import->status == 'processing')
                            <div class="alert alert-info" style="margin-top: 15px; margin-bottom: 0;">
                                <i class="voyager-refresh"></i>
                                This page will auto-refresh every 5 seconds while processing.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Failed Rows -->
                @if($failedRows->count() > 0)
                    <div class="panel panel-bordered panel-danger">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="voyager-warning"></i> Failed Rows ({{ $failedRows->count() }})
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Row #</th>
                                            <th>Errors</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($failedRows as $row)
                                            <tr>
                                                <td>{{ $row->row_number }}</td>
                                                <td>
                                                    @if($row->validation_errors)
                                                        <ul style="margin: 0; padding-left: 15px;">
                                                            @foreach($row->validation_errors as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">No error details</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <code style="font-size: 11px;">{{ json_encode($row->raw_data) }}</code>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($import->failed_rows > 100)
                                <p class="text-muted text-center" style="margin-top: 10px; margin-bottom: 0;">
                                    Showing first 100 failed rows. 
                                    <a href="{{ route('admin.imports.export-errors', $import) }}">
                                        Download full error report
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Actions Sidebar -->
            <div class="col-md-4">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-settings"></i> Actions
                        </h3>
                    </div>
                    <div class="panel-body">
                        <a href="{{ route('admin.imports.download', $import) }}" class="btn btn-primary btn-block">
                            <i class="voyager-download"></i> Download File
                        </a>

                        @if(in_array($import->status, ['failed', 'completed_with_errors']))
                            <form action="{{ route('admin.imports.reprocess', $import) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Reprocess failed rows?')">
                                    <i class="voyager-refresh"></i> Reprocess Failed
                                </button>
                            </form>
                        @endif

                        @if($import->failed_rows > 0)
                            <a href="{{ route('admin.imports.export-errors', $import) }}" class="btn btn-info btn-block">
                                <i class="voyager-list"></i> Export Errors (CSV)
                            </a>
                        @endif

                        @if($import->status == 'completed')
                            <form action="{{ route('admin.imports.rollback', $import) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('This will delete all imported records. Continue?')">
                                    <i class="voyager-trash"></i> Rollback Import
                                </button>
                            </form>
                        @endif

                        <hr>

                        <form action="{{ route('admin.imports.destroy', $import) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Delete this import permanently?')">
                                <i class="voyager-trash"></i> Delete Import
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Import Info -->
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-info-circled"></i> Information
                        </h3>
                    </div>
                    <div class="panel-body">
                        <p><strong>Import ID:</strong> {{ $import->id }}</p>
                        <p><strong>n8n Job ID:</strong> {{ $import->n8n_job_id ?? 'N/A' }}</p>
                        
                        @if($import->started_at)
                            <p><strong>Started:</strong> {{ $import->started_at->diffForHumans() }}</p>
                        @endif
                        
                        @if($import->completed_at && $import->started_at)
                            <p><strong>Duration:</strong> {{ $import->started_at->diffForHumans($import->completed_at, true) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    @if($import->status == 'processing')
        <script>
            // Auto-refresh every 5 seconds while processing
            setTimeout(function() {
                location.reload();
            }, 5000);
        </script>
    @endif
@stop

@section('css')
    <style>
        .dl-horizontal dt {
            width: 120px;
        }
        .dl-horizontal dd {
            margin-left: 140px;
        }
        code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
@stop
