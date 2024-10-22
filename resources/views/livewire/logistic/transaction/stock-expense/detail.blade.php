<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-3">
            <label>Perusahaan</label>
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-company" class="form-select">
                        @if ($company_id)
                            <option value="{{ $company_id }}">{{ $company_text }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <label>Gudang</label>
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-warehouse" class="form-select">
                        @if ($warehouse_id)
                            <option value="{{ $warehouse_id }}">{{ $warehouse_text }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <label>Tanggal Pengeluaran</label>
            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror"
                wire:model="transaction_date" />

            @error('transaction_date')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <label>Catatan</label>
            <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
        </div>
    </div>

    <div class="row">
        {{-- INVOICE DETAIL --}}
        <div class="col-md-12 mb-3">
            <label>Data Permintaan</label>

            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-product" class="form-select">
                    </select>
                </div>
            </div>

            @foreach ($stockExpenseProducts as $index => $item)
                <div class="row align-items-end">
                    <div class="col-auto">
                        <button type="button" class="btn btn-icon btn-danger"
                            wire:click="removeDetail({{ $index }})">
                            <i class="ki-solid ki-abstract-11"></i>
                        </button>
                    </div>

                    <div class="col-md-4">
                        <label class='fw-bold'>Produk</label>
                        <input class="form-control" value="{{ $item['product_text'] }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class='fw-bold'>Jumlah</label>
                        <div class="input-group">
                            <input type="text" class="form-control currency"
                                wire:model.blur="stockExpenseProducts.{{ $index }}.quantity" />

                            <select class="form-select @error('type') is-invalid @enderror"
                                wire:model.blur="stockExpenseProducts.{{ $index }}.unit_detail_id">
                                @foreach ($item['unit_detail_choice'] as $unit)
                                    <option value="{{ $unit['id'] }}">
                                        {{ $unit['name'] }}
                                        {{ $unit['value_info'] ? "({$unit['value_info']})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <hr>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan
    </button>
</form>

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

@include('js.imask')

@push('js')
    <script>
        $(document).ready(function() {
            initSelect2();
        });

        function initSelect2() {
            // Select2 Company
            $('#select2-company').select2({
                placeholder: "Pilih Perusahaan",
                ajax: {
                    url: "{{ route('stock_expense.get.company') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            access: 1,
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

            $('#select2-company').on('change', async function(e) {
                let data = $('#select2-company').val();
                @this.set('company_id', data);

                $('#select2-warehouse').val("").trigger('change');
            });

            // Select2 Warehouse
            $('#select2-warehouse').select2({
                placeholder: "Pilih Gudang",
                ajax: {
                    url: "{{ route('stock_expense.get.warehouse') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            company_id: $('#select2-company').val(),
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

            $('#select2-warehouse').on('change', async function(e) {
                let data = $('#select2-warehouse').val();
                @this.set('warehouse_id', data);
            });

            // Select2 Product
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                ajax: {
                    url: "{{ route('stock_request.get.product') }}",
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

            $('#select2-product').on('change', async function(e) {
                let data = $('#select2-product').val();
                if (data) {
                    @this.call('addDetail', data);
                    $('#select2-product').val('').trigger('change');
                }
            });
        }
    </script>
@endpush
