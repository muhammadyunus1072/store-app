<form wire:submit="store">
    <div class='row'>
        {{-- SELECT COMPANY --}}
        <div class="col-md-4 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}">
            <label>Perusahaan</label>
            <select class="form-select w-100">
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
            <select class="form-select w-100">
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
                @if ($supplierId)
                    <option value="{{ $supplierId }}">{{ $supplierText }}</option>
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

        {{-- SUPPLIER INVOICE NUMBER --}}
        <div class="col-md-4 mb-3">
            <label>Nomor Nota Supplier</label>
            <input type="text" class="form-control @error('supplierInvoiceNumber') is-invalid @enderror"
                wire:model="supplierInvoiceNumber" />

            @error('supplierInvoiceNumber')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- NOTE --}}
        <div class="col-md-12 mb-3">
            <label>Catatan</label>
            <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
        </div>
    </div>

    {{-- PRODUCTS --}}
    <label>Barang-barang yang diterima</label>
    <div class="col-md-12 mb-4" wire:ignore>
        <select id="select2-product" class="form-select w-100">
        </select>
    </div>

    <table class='table gy-1 gx-2'>
        @php
            $total = 0;
            $total_ppn = 0;
        @endphp

        @foreach ($purchaseOrderProducts as $index => $item)
            @php
                $qty = str_replace(',', '.', str_replace('.', '', $item['quantity']));
                $price = str_replace(',', '.', str_replace('.', '', $item['price']));
                $subtotal = $qty * $price;
                $total += $subtotal;
                $total_ppn += $item['is_ppn'] ? ($subtotal * $taxPpnValue) / 100 : 0;
            @endphp

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
                            wire:model.blur="purchaseOrderProducts.{{ $index }}.quantity" />

                        <select class="form-select @error('type') is-invalid @enderror"
                            wire:model.blur="purchaseOrderProducts.{{ $index }}.unit_detail_id">
                            @foreach ($item['unit_detail_choice'] as $unit)
                                <option value="{{ $unit['id'] }}">
                                    {{ $unit['name'] }}
                                    {{ $unit['value_info'] ? "({$unit['value_info']})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>

                {{-- PRICE PER UNIT --}}
                <td style="width: 25%;">
                    <label class='fw-bold'>Harga Satuan</label>
                    <input type="text" class="form-control currency"
                        wire:model.blur="purchaseOrderProducts.{{ $index }}.price" />
                </td>

                {{-- TAX --}}
                <td style="width: 2%">
                    <label class='fw-bold'>Pajak</label>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="" id="ppn_{{ $index }}"
                            wire:model.live="purchaseOrderProducts.{{ $index }}.is_ppn">
                        <label class="form-check-label" for="ppn_{{ $index }}">
                            PPN
                        </label>
                    </div>
                </td>

                {{-- SUBTOTAL --}}
                <td>
                    <label class='fw-bold'>Subtotal</label>
                    <input class='form-control text-end' value="@currency($subtotal)" disabled>
                </td>
            </tr>

            {{-- OPTIONAL ATTIRBUTE --}}
            @if ($isInputProductCode || $isInputProductBatch || $isInputProductExpiredDate)
                <tr>
                    <td></td>

                    {{-- CODE --}}
                    @if ($isInputProductCode)
                        <td>
                            <label class='fw-bold'>Kode Barang</label>
                            <input type="text" class="form-control"
                                wire:model="purchaseOrderProducts.{{ $index }}.code" />
                        </td>
                    @endif

                    {{-- BATCH --}}
                    @if ($isInputProductBatch)
                        <td>
                            <label class='fw-bold'>Kode Produksi</label>
                            <input type="text" class="form-control"
                                wire:model="purchaseOrderProducts.{{ $index }}.batch" />
                        </td>
                    @endif

                    {{-- EXPIRED DATE --}}
                    @if ($isInputProductExpiredDate)
                        <td>
                            <label class='fw-bold'>Tanggal Expired</label>
                            <input type="date"
                                class="form-control @error('purchaseOrderProducts.{{ $index }}.expired_date') is-invalid @enderror"
                                wire:model="purchaseOrderProducts.{{ $index }}.expired_date" />
                            @error('purchaseOrderProducts.{{ $index }}.expired_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </td>
                    @endif
                </tr>
            @endif

            @if ($isInputProductAttachment)
                <tr>
                    <td></td>
                    <td colspan="5">
                        {{-- ATTACHMENTS --}}
                        <label class='fw-bold'>Lampiran</label>
                        <input id="fileInput_{{ $index }}" class="form-control d-none" type="file"
                            wire:model="purchaseOrderProducts.{{ $index }}.files" multiple>

                        <button type="button" class="btn btn-info btn-sm"
                            onclick="$('#fileInput_{{ $index }}').click()" wire:loading.attr="disabled"
                            wire:target='purchaseOrderProducts.{{ $index }}.files'>
                            <div wire:loading wire:target='purchaseOrderProducts.{{ $index }}.files'>
                                <span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                Loading...
                            </div>

                            <div wire:loading.class="d-none"
                                wire:target='purchaseOrderProducts.{{ $index }}.files'>
                                <i class="ki-solid ki-double-up"></i>
                                Tambah Lampiran
                            </div>
                        </button>

                        @error('files.*')
                            <span class="error">{{ $message }}</span>
                        @enderror

                        <div class="mt-3">
                            @if ($purchaseOrderProducts[$index]['uploadedFiles'])
                                <div class="row mb-2">
                                    <div class="col-md-6"><label>Catatan</label></div>
                                    <div class="col-md-6"><label>File</label></div>
                                </div>

                                <div class="row">
                                    @foreach ($purchaseOrderProducts[$index]['uploadedFiles'] as $fileIndex => $file)
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control form-control-sm" cols="30"
                                                rows="4"
                                                wire:model="purchaseOrderProducts.{{ $index }}.uploadedFiles.{{ $fileIndex }}.note" />
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <a href="{{ $file['url'] }}" target="_blank"
                                                class="btn btn-info btn-sm">
                                                <i class="ki-solid ki-file"></i>
                                                {{ $file['original_file_name'] }}
                                            </a>
                                            <button type="button" class="btn btn-icon btn-danger btn-sm"
                                                wire:click="removeFile('{{ $index }}', '{{ $fileIndex }}')">
                                                <i class="ki-solid ki-abstract-11"></i>
                                            </button>
                                        </div>
                                        <hr class="d-md-none">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endif

            {{-- LINE SEPARATOR --}}
            <tr class='border-top'>
                <td></td>
            </tr>
        @endforeach


        @if (count($purchaseOrderProducts) > 0)
            <tr>
                <td class='fs-4 text-end fw-bold align-middle' colspan="5">
                    Subtotal
                </td>
                <td>
                    <input class='form-control text-end fw-bold' value="@currency($total)" disabled>
                </td>
            </tr>

            <tr>
                <td class='fs-4 text-end fw-bold align-middle' colspan="5">
                    PPN ({{ $taxPpnValue }}%)
                </td>
                <td>
                    <input class='form-control text-end fw-bold' value="@currency($total_ppn)" disabled>
                </td>
            </tr>

            <tr>
                <td class='fs-4 text-end fw-bold align-middle' colspan="5">
                    Total
                </td>
                <td>
                    <input class='form-control text-end fw-bold' value="@currency($total_ppn + $total)" disabled>
                </td>
            </tr>
        @endif
    </table>

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
        $(() => {
            // Select2 Supplier
            $('#select2-supplier').select2({
                placeholder: "Pilih Supplier",
                ajax: {
                    url: "{{ route('purchase_order.get.supplier') }}",
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

            // Select2 Product
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                ajax: {
                    url: "{{ route('purchase_order.get.product') }}",
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
        });
    </script>
@endpush
