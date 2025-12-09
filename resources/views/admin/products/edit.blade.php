@extends('voyager::master')

@section('page_title', 'Edit Product')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-title">
                    <i class="voyager-edit"></i> Edit Product: {{ $product->name }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-edit"></i> Product Information
                        </h3>
                        <div class="panel-actions">
                            <a href="{{ route('admin.products-list.show', $product) }}" class="btn btn-sm btn-default">
                                <i class="voyager-eye"></i> View
                            </a>
                            <a href="{{ route('admin.products-list.index') }}" class="btn btn-sm btn-default">
                                <i class="voyager-list"></i> Back to List
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="panel-body">
                        <form action="{{ route('admin.products-list.update', $product) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                {{-- Basic Information --}}
                                <div class="col-md-6">
                                    <h4 style="margin-top: 0;">Basic Information</h4>
                                    
                                    <div class="form-group @error('name') has-error @enderror">
                                        <label for="name">Product Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="{{ old('name', $product->name) }}" required>
                                        @error('name')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('name_arabic') has-error @enderror">
                                        <label for="name_arabic">Product Name (Arabic)</label>
                                        <input type="text" class="form-control" id="name_arabic" name="name_arabic" 
                                               value="{{ old('name_arabic', $product->name_arabic) }}" dir="rtl">
                                        @error('name_arabic')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('sku') has-error @enderror">
                                        <label for="sku">SKU</label>
                                        <input type="text" class="form-control" id="sku" name="sku" 
                                               value="{{ old('sku', $product->sku) }}">
                                        @error('sku')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('category') has-error @enderror">
                                        <label for="category">Category</label>
                                        <select class="form-control" id="category" name="category">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat }}" 
                                                    {{ old('category', $product->category) == $cat ? 'selected' : '' }}>
                                                    {{ $cat }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('category_arabic') has-error @enderror">
                                        <label for="category_arabic">Category (Arabic)</label>
                                        <input type="text" class="form-control" id="category_arabic" name="category_arabic" 
                                               value="{{ old('category_arabic', $product->category_arabic) }}" dir="rtl">
                                        @error('category_arabic')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('type_english') has-error @enderror">
                                        <label for="type_english">Type</label>
                                        <input type="text" class="form-control" id="type_english" name="type_english" 
                                               value="{{ old('type_english', $product->type_english) }}">
                                        @error('type_english')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Pricing and Details --}}
                                <div class="col-md-6">
                                    <h4 style="margin-top: 0;">Pricing & Details</h4>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group @error('currency') has-error @enderror">
                                                <label for="currency">Currency</label>
                                                <select class="form-control" id="currency" name="currency">
                                                    <option value="EGP" {{ old('currency', $product->currency) == 'EGP' ? 'selected' : '' }}>EGP</option>
                                                    <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                                    <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                </select>
                                                @error('currency')
                                                    <span class="help-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group @error('price') has-error @enderror">
                                                <label for="price">Price</label>
                                                <input type="number" class="form-control" id="price" name="price" 
                                                       value="{{ old('price', $product->price) }}" step="0.01" min="0">
                                                @error('price')
                                                    <span class="help-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group @error('manufacturer') has-error @enderror">
                                        <label for="manufacturer">Manufacturer</label>
                                        <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                               value="{{ old('manufacturer', $product->manufacturer) }}">
                                        @error('manufacturer')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('weight') has-error @enderror">
                                        <label for="weight">Weight</label>
                                        <input type="text" class="form-control" id="weight" name="weight" 
                                               value="{{ old('weight', $product->weight) }}" placeholder="e.g., 500g">
                                        @error('weight')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group @error('shelf_life') has-error @enderror">
                                        <label for="shelf_life">Shelf Life</label>
                                        <input type="text" class="form-control" id="shelf_life" name="shelf_life" 
                                               value="{{ old('shelf_life', $product->shelf_life) }}" placeholder="e.g., 12 months">
                                        @error('shelf_life')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Descriptions --}}
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-6">
                                    <div class="form-group @error('description') has-error @enderror">
                                        <label for="description">Description (English)</label>
                                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                        @error('description')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group @error('description_arabic') has-error @enderror">
                                        <label for="description_arabic">Description (Arabic)</label>
                                        <textarea class="form-control" id="description_arabic" name="description_arabic" 
                                                  rows="4" dir="rtl">{{ old('description_arabic', $product->description_arabic) }}</textarea>
                                        @error('description_arabic')
                                            <span class="help-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Buttons --}}
                            <div class="row" style="margin-top: 30px;">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="voyager-check"></i> Update Product
                                    </button>
                                    <a href="{{ route('admin.products-list.show', $product) }}" class="btn btn-default">
                                        <i class="voyager-x"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .panel-actions {
            float: right;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .help-block {
            color: #a94442;
            font-size: 12px;
        }
        .text-danger {
            color: #a94442;
        }
    </style>
@endsection
