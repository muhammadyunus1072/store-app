@extends('app.layouts.panel')

@section('title', 'Persetujuan - Detail')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Persetujuan</h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
            <li class="breadcrumb-item text-muted">Persetujuan</li>
            {{-- <li class="breadcrumb-item">
                <span class="bullet bg-gray-400 w-5px h-2px"></span>
            </li> --}}
        </ul>
        <!--end::Breadcrumb-->

        <div class='row'>
            <div class="col-md-auto mt-2">
                <button class="btn btn-info" onclick="history.back();">
                    <i class="ki-duotone ki-arrow-left fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Kembali
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class='row'>
        <div class='col-md-7'>
            <div class="card">
                <div class="card-header">
                    <h4 class='card-title'>Dokumen Persetujuan</h4>
                </div>
                <div class="card-body">
                    <livewire:document.transaction.approval.remarks-document :approvalId="$objId" />
                </div>
            </div>
        </div>

        <div class='col-md-5'>
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class='card-title'>Riwayat Persetujuan</h4>
                </div>
                <div class="card-body">
                    <livewire:document.transaction.approval-status.datatable :approvalId="$objId" />
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class='card-title'>Tindak Lanjut Persetujuan</h4>
                </div>
                <div class="card-body">
                    <livewire:document.transaction.approval-status.create :approvalId="$objId" />
                </div>
            </div>
        </div>
    </div>
@stop
