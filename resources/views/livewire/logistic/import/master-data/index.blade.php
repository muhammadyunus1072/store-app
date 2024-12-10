<div>
    <h4>Import Master Data</h4>
    <hr>
    @include('livewire.components.import-excel-file', ['import_excel' => $import_excel])

    <div class="row">
        <div class="col-md-4 border rounded p-4">
            <label>Import Master Data Gudang</label>
            <hr>
            <button type="button" class="btn btn-primary" wire:click="syncDataWarehouse" wire:loading.attr="disabled"
                {{ $syncWarehouse ? 'disabled' : null }}>
                @if ($syncWarehouse)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading ({{ $syncWarehouse->progress ? $syncWarehouse->progress : 0 }} / {{ $syncWarehouse->total }})
                @else
                    <i class="fa fa-sync"></i>
                    Sync Data Gudang
                @endif
            </button>
        </div>

        <div class="col-md-4 border rounded p-4">
            <label>Import Master Data Supplier</label>
            <hr>
            <button type="button" class="btn btn-primary" wire:click="syncDataSupplier" wire:loading.attr="disabled"
                {{ $syncSupplier ? 'disabled' : null }}>
                @if ($syncSupplier)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading ({{ $syncSupplier->progress ? $syncSupplier->progress : 0 }} / {{ $syncSupplier->total }})
                @else
                    <i class="fa fa-sync"></i>
                    Sync Data Supplier
                @endif
            </button>
        </div>
    </div>
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
