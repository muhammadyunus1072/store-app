@extends('app.layouts.panel')

@section('title', 'Permintaan Barang')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Permintaan Barang</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Permintaan Barang</li>
            {{-- <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li> --}}
        </ul>
        <!--end::Breadcrumb-->

        @can(PermissionHelper::transform(AccessLogistic::STOCK_REQUEST, PermissionHelper::TYPE_CREATE))
            <div class='row'>
                <div class="col-md-auto mt-2">
                    <a class="btn btn-success" href="{{ route('stock_request.create') }}">
                        <i class="ki-duotone ki-plus fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Tambah Baru
                    </a>
                </div>
            </div>
        @endcan
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <livewire:logistic.filter
            :filterDateStart="true" 
            :filterDateEnd="true"
            {{-- :filterLocationType="App\Models\Logistic\Master\DisplayRack\DisplayRack::class" --}}
            :locationType="App\Models\Logistic\Master\DisplayRack\DisplayRack::class"
            :filterWarehouse="false"
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')">
        </div>
        <div class="card-body">
            <livewire:logistic.transaction.stock-request.datatable 
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')"
            lazy>
        </div>
    </div>
@stop
