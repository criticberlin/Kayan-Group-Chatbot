@extends('voyager::master')

@section('page_title', 'Upload Import')

@section('page_header')
<h1 class="page-title">
    <i class="voyager-upload"></i> Upload Excel Import
</h1>
@stop

@section('content')
<div class="page-content edit-add container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="icon wb-upload"></i> Upload File</h3>
                </div>

                <div class="panel-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.imports.store') }}" method="POST" enctype="multipart/form-data"
                        role="form">
                        @csrf

                        <div class="form-group">
                            <label for="file" class="control-label">
                                <span class="text-danger">*</span> Excel File
                            </label>
                            <input type="file" class="form-control" name="file" id="file" accept=".xlsx,.xls,.csv"
                                required>
                            <small class="help-block">
                                <i class="voyager-info-circled"></i>
                                Supported formats: XLSX, XLS, CSV. Maximum size: 10MB
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="data_type" class="control-label">
                                <span class="text-danger">*</span> Data Type
                            </label>
                            <select name="data_type" id="data_type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                @foreach($dataTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('data_type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="help-block">
                                <i class="voyager-info-circled"></i>
                                Select the type of data in your Excel file
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="category" class="control-label">
                                Category (Optional)
                            </label>
                            <input type="text" class="form-control" name="category" id="category"
                                value="{{ old('category') }}" placeholder="e.g., Electronics, Q1 2024, etc.">
                            <small class="help-block">
                                <i class="voyager-info-circled"></i>
                                Optional categorization for organization
                            </small>
                        </div>

                        @if(auth()->user()->hasPermission('browse_all_departments'))
                            <div class="form-group">
                                <label for="department_id" class="control-label">
                                    Department (Optional)
                                </label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">-- Use My Department --</option>
                                    @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="help-block">
                                    <i class="voyager-info-circled"></i>
                                    Override department assignment (defaults to your department)
                                </small>
                            </div>
                        @endif

                        <hr>

                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <i class="voyager-info-circled"></i> Import Process
                                </h4>
                            </div>
                            <div class="panel-body">
                                <ol style="margin-bottom: 0; padding-left: 20px;">
                                    <li>File is uploaded and validated</li>
                                    <li>Excel data is parsed in the background</li>
                                    @if(feature('n8n_integration', true))
                                        <li>Data is sent to n8n for validation</li>
                                        <li>Validated rows are imported into the database</li>
                                    @else
                                        <li>Data is imported directly into the database</li>
                                    @endif
                                    <li>You can track progress on the details page</li>
                                </ol>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="voyager-upload"></i>
                                <span>Upload & Start Import</span>
                            </button>

                            <a href="{{ route('admin.imports.index') }}" class="btn btn-default">
                                <i class="voyager-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
    document.querySelector('form').addEventListener('submit', function () {
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="voyager-refresh"></i> Uploading...';
    });

    // File size validation
    document.getElementById('file').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (file) {
            var maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size exceeds 10MB. Please choose a smaller file.');
                e.target.value = '';
            }
        }
    });
</script>
@stop