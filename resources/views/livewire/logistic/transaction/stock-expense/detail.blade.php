<div>
    <form wire:submit="store">
        <div class='row'>
            {{-- SELECT COMPANY --}}
            <div class="col-md-4 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}">
                <label>Perusahaan</label>
                <select class="form-select w-100" wire:model.live='companyId'>
                    @php $isFound = false; @endphp

                    @foreach ($companies as $company)
                        @php $isFound = $isFound || $company['id'] == $companyId; @endphp
                        <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                    @endforeach

                    @if (!$isFound && !empty($companyId))
                        <option value="{{ $companyId }}" selected>{{ $companyText }}</option>
                    @endif
                </select>
            </div>

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
        <label>Barang-barang yang dikeluarkan</label>
        <div class="col-md-12 mb-4" wire:ignore>
            <select id="select2-product" class="form-select w-100">
            </select>
        </div>

        <table class='table gy-1 gx-2'>
            @foreach ($stockExpenseProducts as $index => $item)
                @php
                    $qty = NumberFormatter::imaskToValue($item['quantity']);
                    $qtyOld = NumberFormatter::imaskToValue($item['old_quantity']);
                    $qtyChange = $qty - $qtyOld;
                    $rowClass =
                        $qtyChange == 0
                            ? ''
                            : ($qtyChange <= $item['current_stock']
                                ? 'table-success'
                                : 'table-danger');
                @endphp

                <tr class="{{ $rowClass }}">
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
                    </td>

                    {{-- CURRENT STOCK --}}
                    <td style="width: 25%">
                        <label class='fw-bold'>Stok Sekarang</label>
                        <div class="input-group">
                            <input type="text" class="form-control"
                                wire:model="stockExpenseProducts.{{ $index }}.current_stock" disabled />
                            <input type="text" class="form-control"
                                wire:model="stockExpenseProducts.{{ $index }}.current_stock_unit_name"
                                disabled />
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
                            :remarksType="$historyRemarksType" :isShowStock="false" />
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
            initSelect2();
        });

        function initSelect2() {
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
