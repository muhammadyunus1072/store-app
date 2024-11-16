<div>
    <form wire:submit="store">
        <div class='row'>
            {{-- NUMBER --}}
            @if ($number)
                <div class="col-md-4 mb-3">
                    <label>Nomor</label>
                    <input type="text" class='form-control' value="{{ $number }}" disabled>
                </div>
            @endif

            {{-- TRANSACTION DATE --}}
            <div class="col-md-4 mb-3">
                <label>Tanggal</label>
                <input type="date" class="form-control @error('transactionDate') is-invalid @enderror"
                    wire:model="transactionDate" {{ $isShow ? 'disabled' : '' }} />

                @error('transactionDate')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class='row'>
            {{-- SELECT COMPANY REQUESTER --}}
            <div class="col-md-6 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}">
                <label>Perusahaan Peminta</label>
                <select class="form-select w-100" wire:model='destinationCompanyId' {{ $isShow ? 'disabled' : '' }}>
                    @php $isFound = false; @endphp

                    @foreach ($destinationCompanies as $company)
                        @php $isFound = $isFound || $company['id'] == $destinationCompanyId; @endphp
                        <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                    @endforeach

                    @if (!$isFound && !empty($destinationCompanyId))
                        <option value="{{ $destinationCompanyId }}" selected>{{ $destinationCompanyText }}</option>
                    @endif
                </select>
            </div>

            {{-- SELECT WAREHOUSE REQUESTER --}}
            <div class="col-md-6 mb-3">
                <label>Gudang Peminta</label>
                <select class="form-select w-100" wire:model='destinationWarehouseId' {{ $isShow ? 'disabled' : '' }}>
                    @php $isFound = false; @endphp

                    @foreach ($destinationWarehouses as $warehouse)
                        @php $isFound = $isFound || $warehouse['id'] == $destinationWarehouseId; @endphp
                        <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                    @endforeach

                    @if (!$isFound && !empty($destinationWarehouseId))
                        <option value="{{ $destinationWarehouseId }}" selected>{{ $destinationWarehouseText }}
                        </option>
                    @endif
                </select>
            </div>

            {{-- SELECT COMPANY REQUESTED --}}
            <div class="col-md-6 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}" wire:ignore>
                <label>Perusahaan Diminta</label>
                <select class="form-select w-100" id="select2-company-source" {{ $isShow ? 'disabled' : '' }}>
                    @if (!empty($sourceCompanyId))
                        <option value="{{ $sourceCompanyId }}" selected>{{ $sourceCompanyText }}</option>
                    @endif
                </select>
            </div>

            {{-- SELECT WAREHOUSE REQUESTED --}}
            <div class="col-md-6 mb-3" wire:ignore>
                <label>Gudang Diminta</label>
                <select class="form-select w-100" id="select2-warehouse-source" {{ $isShow ? 'disabled' : '' }}>
                    @if (!empty($sourceWarehouseId))
                        <option value="{{ $sourceWarehouseId }}" selected>{{ $sourceWarehouseText }}</option>
                    @endif
                </select>
            </div>

            {{-- NOTE --}}
            <div class="col-md-12 mb-4">
                <label>Catatan</label>
                <textarea class="form-control" cols="30" rows="4" wire:model="note" {{ $isShow ? 'disabled' : '' }}></textarea>
            </div>
        </div>

        {{-- PRODUCTS --}}
        <div class="{{ empty($sourceWarehouseId) ? 'd-none' : '' }}">
            <label>Barang-barang yang diminta</label>
            <div class="col-md-12 mb-4  {{ $isShow ? 'd-none' : '' }}" wire:ignore>
                <select id="select2-product" class="form-select w-100">
                </select>
            </div>
        </div>

        <table class='table gy-1 gx-2'>
            @foreach ($stockRequestProducts as $index => $item)
                <tr class="{{ $item['row_color_class'] }}">
                    {{-- ACTION --}}
                    <td style="width: 2%" class='align-bottom'>
                        @if (!$isShow)
                            <label class='fw-bold'>Aksi</label>
                            <button type="button"
                                class="btn btn-outline btn-outline-dashed btn-outline-secondary btn-active-light-secondary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button type="button" class="dropdown-item text-danger"
                                        wire:click="removeDetail({{ $index }})">
                                        <i class="ki-solid ki-abstract-11 text-danger"></i>
                                        Hapus
                                    </button>
                                </li>
                            </ul>
                        @endif
                    </td>

                    {{-- NAME --}}
                    <td style="width: 25%;">
                        <label class='fw-bold'>Produk</label>
                        <input class='form-control' value="{{ $item['product_text'] }}" disabled>
                    </td>

                    {{-- QUANTITY & UNIT --}}
                    <td style="width: 25%">
                        <label class='fw-bold'>Jumlah</label>
                        <div class="input-group">
                            <input type="text" class="form-control currency"
                                wire:model.blur="stockRequestProducts.{{ $index }}.quantity"
                                {{ $isShow ? 'disabled' : '' }} />

                            <select class="form-select @error('type') is-invalid @enderror"
                                wire:model.blur="stockRequestProducts.{{ $index }}.unit_detail_id"
                                {{ $isShow ? 'disabled' : '' }}>
                                @foreach ($item['unit_detail_choice'] as $unit)
                                    <option value="{{ $unit['id'] }}">
                                        {{ $unit['name'] }}
                                        {{ $unit['value_info'] ? "({$unit['value_info']})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    {{-- CURRENT STOCK --}}
                    @if (!$isShow)
                        <td style="width: 25%">
                            <label class='fw-bold'>Stok Sekarang</label>
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    wire:model="stockRequestProducts.{{ $index }}.current_stock" disabled />
                                <input type="text" class="form-control"
                                    wire:model="stockRequestProducts.{{ $index }}.current_stock_unit_name"
                                    disabled />
                            </div>
                        </td>
                    @endif
                </tr>

                {{-- LINE SEPARATOR --}}
                <tr class='border-top'>
                    <td></td>
                </tr>
            @endforeach
        </table>

        @if (!$isShow)
            <button type="submit" class="btn btn-success mt-3">
                <i class='ki-duotone ki-check fs-1'></i>
                Simpan
            </button>
        @endif
    </form>
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

@include('js.imask')

@push('js')
    <script>
        $(document).ready(function() {
            // Select2 Company Source
            $('#select2-company-source').select2({
                placeholder: "Pilih Perusahaan Diminta",
                ajax: {
                    url: "{{ route('stock_request.get.company') }}",
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

            $('#select2-company-source').on('change', async function(e) {
                let data = $('#select2-company-source').val();
                @this.set('sourceCompanyId', data);
            });

            // Select2 Warehouse Source
            $('#select2-warehouse-source').select2({
                placeholder: "Pilih Permintaan Gudang",
                ajax: {
                    url: "{{ route('stock_request.get.warehouse') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            product_stock: 1,
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

            $('#select2-warehouse-source').on('change', async function(e) {
                let data = $('#select2-warehouse-source').val();
                @this.set('sourceWarehouseId', data);
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
                            product_stock: 1,
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

            $('#select2-product').on('select2:select', function(e) {
                let data = $('#select2-product').val();
                if (data) {
                    @this.call('addDetail', data);
                    $('#select2-product').val('').trigger('change');
                }
            });
        });
    </script>
@endpush
