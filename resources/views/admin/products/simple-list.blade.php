@extends('voyager::master')

@section('page_title', 'Products')

@section('page_header')
<h1 class="page-title">
    <i class="voyager-bag"></i> Products
    <small>Showing {{ $products->total() }} products</small>
</h1>
@stop

@section('content')
<div class="page-content browse container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    {{-- Search and Filters --}}
                    <form method="GET" class="form-inline" style="margin-bottom: 20px;">
                        <div class="form-group" style="margin-right: 10px;">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by name, SKU, or category..." style="width: 300px;"
                                value="{{ request('search') }}">
                        </div>
                        <div class="form-group" style="margin-right: 10px;">
                            <select name="category" class="form-control" style="width: 200px;">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-right: 10px;">
                            <select name="is_active" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-right: 5px;">
                            <i class="voyager-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.products-list.index') }}" class="btn btn-default">
                            <i class="voyager-x"></i> Clear
                        </a>
                    </form>

                    {{-- Products Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Type</th>
                                    <th>Manufacturer</th>
                                    <th>Unit Barcode</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $specs = is_string($product->specifications)
                                            ? json_decode($product->specifications, true)
                                            : $product->specifications;
                                    @endphp
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td><strong>{{ $product->name }}</strong></td>
                                        <td><small>{{ $product->sku }}</small></td>
                                        <td><span class="label label-info">{{ $product->category ?? 'N/A' }}</span></td>
                                        <td><strong>{{ $product->currency }}
                                                {{ number_format($product->price, 2) }}</strong></td>
                                        <td>{{ $specs['type'] ?? 'N/A' }}</td>
                                        <td>{{ $specs['manufacturer'] ?? 'N/A' }}</td>
                                        <td><small>{{ $specs['unit_barcode'] ?? 'N/A' }}</small></td>
                                        <td>
                                            @if($product->is_active)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Inactive</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $product->created_at->format('Y-m-d') }}</small></td>
                                        <td>
                                            <a href="{{ route('admin.products-list.show', $product) }}"
                                                class="btn btn-sm btn-primary" title="View Details">
                                                <i class="voyager-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.products-list.edit', $product) }}"
                                                class="btn btn-sm btn-warning" title="Edit Product">
                                                <i class="voyager-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center" style="padding: 40px;">
                                            <i class="voyager-search" style="font-size: 48px; color: #ccc;"></i>
                                            <br><br>
                                            No products found matching your criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="clearfix" style="margin-top: 20px;">
                        <div class="pull-left">
                            <p class="text-muted" style="margin-top: 7px;">
                                Showing <strong>{{ $products->firstItem() ?? 0 }}</strong> to
                                <strong>{{ $products->lastItem() ?? 0 }}</strong>
                                of <strong>{{ $products->total() }}</strong> products
                            </p>
                        </div>
                        <div class="pull-right">
                            @if ($products->hasPages())
                                <ul class="pagination" style="margin: 0;">
                                    {{-- Previous Page Link --}}
                                    @if ($products->onFirstPage())
                                        <li class="disabled"><span>« Previous</span></li>
                                    @else
                                        <li>
                                            <a href="{{ $products->appends(request()->query())->previousPageUrl() }}"
                                                rel="prev">
                                                « Previous
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                        @if ($page == $products->currentPage())
                                            <li class="active"><span>{{ $page }}</span></li>
                                        @else
                                            <li>
                                                <a href="{{ $products->appends(request()->query())->url($page) }}">
                                                    {{ $page }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($products->hasMorePages())
                                        <li>
                                            <a href="{{ $products->appends(request()->query())->nextPageUrl() }}" rel="next">
                                                Next »
                                            </a>
                                        </li>
                                    @else
                                        <li class="disabled"><span>Next »</span></li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop