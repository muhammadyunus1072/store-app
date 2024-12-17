@extends('app.layouts.panel')

@section('title', 'Interkoneksi Sakti KBKI')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Interkoneksi Sakti KBKI</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Interkoneksi Sakti KBKI</li>
            {{-- <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li> --}}
        </ul>
        <!--end::Breadcrumb-->

        @can(PermissionHelper::transform(AccessInterkoneksiSakti::INTERKONEKSI_SAKTI_KBKI, PermissionHelper::TYPE_CREATE))
            <div class='row'>
                <div class="col-md-auto mt-2">
                    <a class="btn btn-success" href="{{ route('interkoneksi_sakti_kbki.create') }}">
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
        <div class="card-body">
            <livewire:rsmh.sakti.interkoneksi-sakti-kbki.datatable lazy>
        </div>
    </div>
@stop
