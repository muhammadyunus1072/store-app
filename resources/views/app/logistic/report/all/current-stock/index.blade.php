@extends('app.layouts.panel')

@section('title', 'Stok Akhir')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Stok Akhir</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Stok Akhir</li>
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
            :filterDateStart="true" 
            :filterDateEnd="true" 
            :filterProductMultiple="true" 
            :filterCategoryProductMultiple="true"
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')">
        </div>
        <div class="card-body">
            <livewire:logistic.report.all.current-stock.datatable-header
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')">

            <livewire:logistic.report.all.current-stock.datatable 
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')"
            lazy>
        </div>
    </div>
@stop
