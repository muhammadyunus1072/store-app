<div class="">
    <h4>Import Stok Awal</h4>
    <hr>

    <div class="row">
        {{-- SELECT WAREHOUSE --}}
        <div class="col-md-4 mb-3">
            <label>Gudang</label>
            <select class="form-select w-100" wire:model.live='warehouseId'>
                @php $isFound = false; @endphp
    
                @foreach ($warehouses as $warehouse)
                    @php $isFound = $isFound || $warehouse['id'] == $warehouseId; @endphp
                    <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                @endforeach
    
                @if (!$isFound && !empty($warehouseId))
                    <option value="{{ $warehouseId }}" selected>{{ $warehouseText }}</option>
                @endif
            </select>
        </div>

        {{-- TRANSACTION DATE --}}
        <div class="col-md-4 mb-3">
            <label>Tanggal</label>
            <input type="date" class="form-control @error('transactionDate') is-invalid @enderror"
                wire:model.live="transactionDate"/>

            @error('transactionDate')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
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
