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

            {{-- SELECT COMPANY --}}
            <div class="col-md-4 mb-3 {{ $isMultipleCompany ? '' : 'd-none' }}">
                <label>Perusahaan</label>
                <select class="form-select w-100" wire:model.live='companyId' {{ $isShow ? 'disabled' : '' }}>
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
                <select class="form-select w-100" wire:model.live='warehouseId' {{ $isShow ? 'disabled' : '' }}>
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
                    wire:model.live="transactionDate" {{ $isShow ? 'disabled' : '' }} />

                @error('transactionDate')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- NOTE --}}
            <div class="col-md-12 mb-4">
                <label>Catatan</label>
                <textarea class="form-control" cols="30" rows="4" wire:model="note" {{ $isShow ? 'disabled' : '' }}></textarea>
            </div>
        </div>

        {{-- PRODUCTS --}}
        <label>Barang-barang detail</label>
        <div class="row d-flex">
            <div class="col-8 col-md-10 mb-4 {{ $isShow ? 'd-none' : '' }}" wire:ignore>
                <select id="select2-product" class="form-select w-100">
                </select>
            </div>
            <div class="col-auto {{ $isShow ? 'd-none' : '' }}">
                <button type="button" class="btn btn-danger" onclick="closeSelect2()">
                    <i class="ki-solid ki-abstract-11"></i>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class='table table-bordered text-nowrap w-100 h-100'>
                <thead>
                    <tr>
                        <th>
                            <div class="fs-6 p-2">Aksi</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Produk</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Stok Sistem</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Stok Nyata</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Selisih</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockOpnameDetails as $index => $item)
                    
                        <tr class="{{ $item['row_color_class'] }}">
                            {{-- ACTION --}}
                            <td class='align-bottom'>
                                @if (!$isShow)
                                    
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-danger "
                                            wire:click="removeDetail({{ $index }})">
                                            <i class="ki-solid ki-abstract-11"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
    
                            {{-- NAME --}}
                            <td>
                                {{ $item['product_text'] }}
                            </td>
    
                            {{-- CURRENT STOCK --}}
                            
                                <td>
                                    <div class="input-group" style="width:170px;">
                                        <input type="text" class="form-control" style="width:60%;"
                                            wire:model="stockOpnameDetails.{{ $index }}.system_stock" disabled />
                                        <input type="text" class="form-control"
                                            wire:model="stockOpnameDetails.{{ $index }}.system_unit_name"
                                            disabled />
                                    </div>
                                </td>
                            
                            {{-- QUANTITY & UNIT --}}
                            <td>
                                <div class="input-group" style="width:170px;">
                                    <input type="text" class="form-control currency" style="width:60%;"
                                        wire:model.live="stockOpnameDetails.{{ $index }}.real_stock"
                                        {{ $isShow ? 'disabled' : '' }} />
                                    <input type="text" class="form-control"
                                        wire:model="stockOpnameDetails.{{ $index }}.system_unit_name"
                                        disabled />
                                </div>
                            </td>
    
                            {{-- CURRENT STOCK --}}
                            
                                <td>
                                    <div class="input-group" style="width:170px;">
                                        <input type="text" class="form-control" style="width:60%;"
                                            wire:model="stockOpnameDetails.{{ $index }}.difference" disabled />
                                        <input type="text" class="form-control"
                                            wire:model="stockOpnameDetails.{{ $index }}.system_unit_name"
                                            disabled />
                                    </div>
                                </td>
                            
    
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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
        function closeSelect2() {
            $('#select2-product').select2('close');
        };
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
            $('#select2-product').select2('open');

            $('#select2-product').on('change', async function(e) {
                let data = $('#select2-product').val();
                if (data) {
                    @this.call('addDetail', data);
                    $('#select2-product').val('').trigger('change');
                }
                setTimeout(() => {
                    $('#select2-product').select2('open');
                }, 200);
            });
        }
    </script>
@endpush
