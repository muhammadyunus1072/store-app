<div class="">
    <div class="row">

        <h4>Sync Data Pengeluaran</h4>
        {{-- SELECT WAREHOUSE --}}
        <div class="col-md-6 mb-3" wire:ignore>
            <label>Gudang</label>
            <select id="select2-warehouse" class="form-select w-100">
            </select>
        </div>
        <div class="col-auto row align-items-end">
            <button type="button" wire:click="syncStockRequest" class="btn btn-primary mt-3 mb-3"
            {{$isSyncStockRequest ? 'disabled' : null}}>
                <i class="fa fa-sync"></i>
                Sync Permintaan
            </button>
        </div>
    </div>

    <h4>Import Data Pengeluaran</h4>
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

@push('js')
    <script>
        $(() => {
            // Select2 Supplier
            $('#select2-warehouse').select2({
                placeholder: "Pilih Gudang",
                ajax: {
                    url: "{{ route('i_stock_request.get.warehouse') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                        };
                    },
                    processResults: function(data) {
                        console.log(data)
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

            $('#select2-warehouse').on('change', async function(e) {
                let data = $('#select2-warehouse').val();
                @this.set('importSourceWarehouseId', data);
            });
        });
    </script>
@endpush
