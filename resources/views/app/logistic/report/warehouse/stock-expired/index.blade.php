@extends('app.layouts.panel')

@section('title', 'Stok Expired Gudang')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Stok Expired Gudang</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Stok Expired Gudang</li>
            {{-- <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li> --}}
        </ul>
        <!--end::Breadcrumb-->

    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <livewire:logistic.filter 
            :filterExpiredDateStart="true"
            :filterExpiredDateEnd="true"
            :filterProductMultiple="true" 
            :filterCategoryProductMultiple="true"
            :filterWarehouse="true"
            :expiredDateStart="Carbon\Carbon::now()->subMonths(3)->format('Y-m-d')"
            :expiredDateEnd="Carbon\Carbon::now()->addMonths(3)->format('Y-m-d')">
        </div>
        
        <div class="card-body">
            <livewire:logistic.report.warehouse.stock-expired.datatable-header 
            :expiredDateStart="Carbon\Carbon::now()->subMonths(3)->format('Y-m-d')"
            :expiredDateEnd="Carbon\Carbon::now()->addMonths(3)->format('Y-m-d')"
            lazy>

            <livewire:logistic.report.warehouse.stock-expired.datatable 
            :expiredDateStart="Carbon\Carbon::now()->subMonths(3)->format('Y-m-d')"
            :expiredDateEnd="Carbon\Carbon::now()->addMonths(3)->format('Y-m-d')"
            lazy>
        </div>
    </div>
@stop

