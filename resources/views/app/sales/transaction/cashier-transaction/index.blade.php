@extends('app.layouts.panel')

@section('title', 'Kasir')

@section('header')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Kasir</h1>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <livewire:sales.transaction.cashier-transaction.index />
        </div>
    </div>
@stop
