<div>
    {{-- GIZI --}}
    <h4>Import Data Pembelian Gizi</h4>
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

        {{-- SELECT SUPPLIER --}}
        <div class="col-md-4 mb-3" wire:ignore>
            <label>Supplier</label>
            <select id="select2-supplier" class="form-select w-100">
            </select>
        </div>

        {{-- TRANSACTION DATE --}}
        <div class="col-md-4 mb-3">
            <label>Tahun Bulan</label>
            <input type="month" class="form-control @error('transactionDate') is-invalid @enderror"
                wire:model.live="periode" />

            @error('periode')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    @include('livewire.components.import-excel-file', ['import_excel' => $import_excel])


    {{-- RUMAH TANGGA --}}
    <h4>Sync Data Pembelian Rumah Tangga</h4>
    <hr>
    <div class="row mb-3 align-items-end">
        {{-- SELECT WAREHOUSE --}}
        <div class="col-md-4 mb-3">
            <label>Gudang</label>
            <select class="form-select w-100" wire:model.live='pembelianRTWarehouseId'>
                @php $isFound = false; @endphp

                @foreach ($warehouses as $warehouse)
                    @php $isFound = $isFound || $warehouse['id'] == $pembelianRTWarehouseId; @endphp
                    <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                @endforeach

                @if (!$isFound && !empty($pembelianRTWarehouseId))
                    <option value="{{ $pembelianRTWarehouseId }}" selected>{{ $warehouseText }}</option>
                @endif
            </select>
        </div>
        <div class="col-auto mb-3">
            <button type="button" wire:click="syncPembelianRT2024" class="btn btn-primary"
                {{ $syncPembelianRT ? 'disabled' : null }}>

                @if ($syncPembelianRT)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    {{ $syncPembelianRT->progress }} / {{ $syncPembelianRT->total }} Loading...
                @else
                    <i class="fa fa-sync"></i>
                    Sync Pembelian Rumah Tangga 2024
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

@push('js')
    <script>
        $(() => {
            // Select2 Supplier
            $('#select2-supplier').select2({
                placeholder: "Pilih Supplier",
                ajax: {
                    url: "{{ route('i_purchase_order.get.supplier') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    "id": item.id,
                                    "text": item.text,
                                }
                            })
                        };
                    },
                },
                cache: true
            });

            $('#select2-supplier').on('change', async function(e) {
                let data = $('#select2-supplier').val();
                @this.set('supplierId', data);
            });
        });
    </script>
@endpush
