@extends('app.layouts.panel')

@section('title', 'Pengeluaran')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Pengeluaran</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Pengeluaran</li>
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
            <livewire:logistic.report.filter 
            :show_input_date_start="true"
            :show_input_date_end="true"
            :show_input_product="true"
            :show_input_category_product="true"
            >
        </div>
        <div class="card-body">
            <livewire:logistic.report.stock-expense.datatable lazy>
        </div>
    </div>
@stop

