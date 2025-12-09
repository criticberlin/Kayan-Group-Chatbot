@extends('voyager::master')

@section('page_title', 'Product Details')

@section('page_header')
<h1 class="page-title">
    <i class="voyager-bag"></i> Product Details
</h1>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    <a href="{{ route('admin.products-list.index') }}" class="btn btn-default"
                        style="margin-bottom: 20px;">
                        <i class="voyager-list"></i> Back to Products List
                    </a>

                    <div class="row">
                        {{-- Left Column --}}
                        <div class="col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-info-circled"></i> Basic Information</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Product ID</th>
                                            <td>{{ $product->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>SKU</th>
                                            <td>{{ $product->sku ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Internal Code</th>
                                            <td>{{ $product->internal_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>{{ $product->serial_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($product->is_active)
                                                    <span class="label label-success">Active</span>
                                                @else
                                                    <span class="label label-default">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-tag"></i> Name & Description</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Name (English)</th>
                                            <td><strong>{{ $product->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Name (Arabic)</th>
                                            <td>{{ $product->name_arabic ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description (English)</th>
                                            <td>{{ $product->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description (Arabic)</th>
                                            <td>{{ $product->description_arabic ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-dollar"></i> Pricing</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Price</th>
                                            <td><strong style="font-size: 18px; color: #26A65B;">
                                                    {{ $product->currency }} {{ number_format($product->price, 2) }}
                                                </strong></td>
                                        </tr>
                                        <tr>
                                            <th>Currency</th>
                                            <td>{{ $product->currency }}</td>
                                        </tr>
                                        <tr>
                                            <th>Stock Quantity</th>
                                            <td>{{ $product->stock_quantity }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="col-md-6">
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-categories"></i> Categories & Types</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Category (English)</th>
                                            <td><span class="label label-info">{{ $product->category ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Category (Arabic)</th>
                                            <td>{{ $product->category_arabic ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type (English)</th>
                                            <td>{{ $product->type_english ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type (Arabic)</th>
                                            <td>{{ $product->type_arabic ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-danger">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-rocket"></i> Marketing & Sales</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Launch Date</th>
                                            <td>{{ $product->launch_date ? \Carbon\Carbon::parse($product->launch_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Market Segment</th>
                                            <td>{{ $product->market_segment ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Target Audience</th>
                                            <td>{{ $product->target_audience ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Selling Channel</th>
                                            <td>{{ $product->selling_channel ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-barcode"></i> Barcodes</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 40%;">Unit Barcode</th>
                                            <td><code>{{ $product->unit_barcode ?? 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Box Barcode</th>
                                            <td><code>{{ $product->box_barcode ?? 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>Carton Barcode</th>
                                            <td><code>{{ $product->carton_barcode ?? 'N/A' }}</code></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Full Width Bottom Section --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-ship"></i> Product Details</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20%;">Manufacturer</th>
                                            <td>{{ $product->manufacturer ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Weight/Unit</th>
                                            <td>{{ $product->weight ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Packaging Details</th>
                                            <td>{{ $product->packaging_details ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Shelf Life</th>
                                            <td>{{ $product->shelf_life ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><i class="voyager-clock"></i> Audit Information</h3>
                                </div>
                                <div class="panel-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20%;">Created At</th>
                                            <td>{{ $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ $product->updated_at ? $product->updated_at->format('Y-m-d H:i:s') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Import ID</th>
                                            <td>{{ $product->import_id ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop