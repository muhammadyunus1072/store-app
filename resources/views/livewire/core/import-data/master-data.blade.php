<div class="">
    <div class="row mb-3">
        <div class="col-auto">
            <button type="button" wire:click="syncSupplier" class="btn btn-primary mt-3"
            {{$isSyncProgress ? 'disabled' : null}}>
                <i class="fa fa-sync"></i>
                Sync Supplier
            </button>
        </div>
    </div>

    <h4>Import Master Data</h4>
    <hr>

    @include('app.components.import-excel-file', ['import_excel' => $import_excel])
</div>

@push('css')
    <style>
        input[type=checkbox] {
            /* Double-sized Checkboxes */
            -ms-transform: scale(1.2);
            /* IE */
            -moz-transform: scale(1.2);
            /* FF */
            -webkit-transform: scale(1.2);
            /* Safari and Chrome */
            -o-transform: scale(1.2);
            /* Opera */
            padding: 8px;
        }
    </style>
@endpush
