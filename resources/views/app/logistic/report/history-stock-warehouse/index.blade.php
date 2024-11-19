@extends('app.layouts.panel')

@section('title', 'Kartu Stok Gudang')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Kartu Stok Gudang</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Kartu Stok Gudang</li>
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
            :filterProduct="true" 
            :filterWarehouse="true"
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')">
        </div>
        
        <div class="card-body">
            <livewire:logistic.report.history-stock-warehouse.index 
            :dateStart="Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')"
            :dateEnd="Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')"
            lazy>
        </div>
    </div>
@stop
