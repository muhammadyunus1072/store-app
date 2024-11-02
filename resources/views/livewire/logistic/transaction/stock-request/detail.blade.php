<div>
    <form wire:submit="store">
        <div class='row'>
            {{-- SELECT COMPANY REQUESTER --}}
            <div class="col-md-6 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}">
                <label>Perusahaan Peminta</label>
                <select class="form-select w-100" wire:model='requesterCompanyId'>
                    @php $isFound = false; @endphp

                    @foreach ($requesterCompanies as $company)
                        @php $isFound = $isFound || $company['id'] == $requesterCompanyId; @endphp
                        <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                    @endforeach

                    @if (!$isFound && !empty($requesterCompanyId))
                        <option value="{{ $requesterCompanyId }}" selected>{{ $requesterCompanyText }}</option>
                    @endif
                </select>
            </div>

            {{-- SELECT WAREHOUSE REQUESTER --}}
            <div class="col-md-6 mb-3">
                <label>Gudang Peminta</label>
                <select class="form-select w-100" wire:model='requesterWarehouseId'>
                    @php $isFound = false; @endphp

                    @foreach ($requesterWarehouses as $warehouse)
                        @php $isFound = $isFound || $warehouse['id'] == $requesterWarehouseId; @endphp
                        <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                    @endforeach

                    @if (!$isFound && !empty($requesterWarehouseId))
                        <option value="{{ $requesterWarehouseId }}" selected>{{ $requesterWarehouseText }}</option>
                    @endif
                </select>
            </div>

            {{-- SELECT COMPANY REQUESTED --}}
            <div class="col-md-6 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}" wire:ignore>
                <label>Perusahaan Diminta</label>
                <select class="form-select w-100" id="select2-company-requested">
                    @if (!empty($requestedCompanyId))
                        <option value="{{ $requestedCompanyId }}" selected>{{ $requestedCompanyText }}</option>
                    @endif
                </select>
            </div>

            {{-- SELECT WAREHOUSE REQUESTED --}}
            <div class="col-md-6 mb-3" wire:ignore>
                <label>Gudang Diminta</label>
                <select class="form-select w-100" id="select2-warehouse-requested">
                    @if (!empty($requestedWarehouseId))
                        <option value="{{ $requestedWarehouseId }}" selected>{{ $requestedWarehouseText }}</option>
                    @endif
                </select>
            </div>

            {{-- TRANSACTION DATE --}}
            <div class="col-md-4 mb-3">
                <label>Tanggal</label>
                <input type="date" class="form-control @error('transactionDate') is-invalid @enderror"
                    wire:model="transactionDate" />

                @error('transactionDate')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- NOTE --}}
            <div class="col-md-12 mb-4">
                <label>Catatan</label>
                <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
            </div>
        </div>

        {{-- PRODUCTS --}}
        <label>Barang-barang yang diminta</label>
        <div class="col-md-12 mb-4" wire:ignore>
            <select id="select2-product" class="form-select w-100">
            </select>
        </div>

        <table class='table gy-1 gx-2'>
            @foreach ($stockRequestProducts as $index => $item)
                {{-- MAIN ATTIRBUTE --}}
                <tr>
                    {{-- ACTION --}}
                    <td style="width: 2%" class='align-bottom'>
                        <label class='fw-bold'>Aksi</label>
                        <button type="button"
                            class="btn btn-outline btn-outline-dashed btn-outline-secondary btn-active-light-secondary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="dropdown-item text-info"
                                    wire:click.prevent="duplicateDetail({{ $index }})">
                                    <i class="ki-solid ki-copy text-info"></i>
                                    Duplikat
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item text-danger"
                                    wire:click="removeDetail({{ $index }})">
                                    <i class="ki-solid ki-abstract-11 text-danger"></i>
                                    Hapus
                                </button>
                            </li>
                        </ul>
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
                                wire:model.blur="stockRequestProducts.{{ $index }}.quantity" />

                            <select class="form-select @error('type') is-invalid @enderror"
                                wire:model.blur="stockRequestProducts.{{ $index }}.unit_detail_id">
                                @foreach ($item['unit_detail_choice'] as $unit)
                                    <option value="{{ $unit['id'] }}">
                                        {{ $unit['name'] }}
                                        {{ $unit['value_info'] ? "({$unit['value_info']})" : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                </tr>

                {{-- LINE SEPARATOR --}}
                <tr class='border-top'>
                    <td></td>
                </tr>
            @endforeach
        </table>

        <button type="submit" class="btn btn-success mt-3">
            <i class='ki-duotone ki-check fs-1'></i>
            Simpan
        </button>
    </form>

    {{-- HISTORY DATATABLE --}}
    @if ($objId)
        <div class="accordion mt-4" id="accordionExample" wire:ignore.self>
            <div class="accordion-item" wire:ignore.self>
                <h2 class="accordion-header" id="headingOne" wire:ignore>
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <label class='fw-bold'>Kartu Stok</label>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                    data-bs-parent="#accordionExample" wire:ignore.self>
                    <div class="accordion-body" wire:ignore.self>
                        <livewire:logistic.transaction.product-detail-history.history-datatable :remarksIds="$historyRemarksIds"
                            :remarksType="$historyRemarksType" />
                    </div>
                </div>
            </div>
        </div>
    @endif
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
            $('#select2-company-requested').select2({
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

            $('#select2-company-requested').on('change', async function(e) {
                let data = $('#select2-company-requested').val();
                @this.set('requestedCompanyId', data);
            });

            // Select2 Warehouse Source
            $('#select2-warehouse-requested').select2({
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

            $('#select2-warehouse-requested').on('change', async function(e) {
                let data = $('#select2-warehouse-requested').val();
                @this.set('requestedWarehouseId', data);
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
