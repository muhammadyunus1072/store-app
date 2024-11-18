<div class='row w-100 py-3'>
    <div class="row">
        <div class="row">
            @if ($filterDateStart)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="dateStart" />
                </div>
            @endif
            @if ($filterDateEnd)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" wire:model.live="dateEnd" />
                </div>
            @endif
        </div>
        <div class="row">
            @if ($show_input_entry_dateStart)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tgl Masuk Mulai</label>
                    <div class="d-flex">
                        <input type="date" class="form-control" wire:model.live="entry_dateStart" />
                        <button class="btn btn-danger ms-2" wire:click="resetEntryDateStart">
                            Reset
                        </button>
                    </div>
                </div>
            @endif

            @if ($show_input_entry_dateEnd)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tgl Masuk Akhir</label>
                    <div class="d-flex">
                        <input type="date" class="form-control" wire:model.live="entry_dateEnd" />
                        <button class="btn btn-danger ms-2" wire:click="resetEntryDateEnd">
                            Reset
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- SELECT PRODUCT --}}
        @if ($filterProduct)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-products" multiple></select>
                </div>
            </div>
        @endif

        {{-- SELECT PRODUCT MULTIPLE --}}
        @if ($filterProductMultiple)
            <div class="col-md-4 mb-3" wire:ignore>
                <label>Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-products" multiple></select>
                </div>
            </div>
        @endif

        {{-- SELECT CATEGORY PRODUCT MULTIPLE --}}
        @if ($filterCategoryProductMultiple)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Kategori Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-category-products" multiple></select>
                </div>
            </div>
        @endif

        @if ($filterWarehouse)
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
        @endif

        @if ($filterSupplier)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Supplier</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-supplier" multiple></select>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if ($show_input_expired_dateStart && $setting_product_expired_date)
            <div class="col-md-4 mb-2">
                <label class="form-label">Expired Date Mulai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expired_dateStart" />
                    <button class="btn btn-danger ms-2" wire:click="resetExpiredDateStart">
                        Reset
                    </button>
                </div>
            </div>
        @endif
        @if ($show_input_expired_dateEnd && $setting_product_expired_date)
            <div class="col-md-4 mb-2">
                <label class="form-label">Expired Date Sampai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expired_dateEnd" />
                    <button class="btn btn-danger ms-2" wire:click="resetExpiredDateEnd">
                        Reset
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- EXPORT DATA --}}
    @if ($showExport)
        <div class="row col-12 my-3">
            <div class="col-auto">
                <label>Export Data:</label>
            </div>
            <div class="col-auto">
                <button class="btn btn-light-success btn-sm"
                    wire:click="$dispatch('datatable-export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_EXCEL }}' })">
                    <i class="fa fa-file-excel"></i>
                    Export Excel
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-light-danger btn-sm"
                    wire:click="$dispatch('datatable-export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_PDF }}' })">
                    <i class="fa fa-file-pdf"></i>
                    Export PDF
                </button>
            </div>
        </div>
    @endif
</div>

@push('js')
    <script>
        $(() => {
            $('#select2-products').select2({
                placeholder: "Seluruh Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route('find.product') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                            ignore_access: 1,
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
                }
            });

            $('#select2-products').on('select2:select', function(e) {
                @this.call('selectProducts', e.params.data)
            });

            $('#select2-products').on('select2:unselect', function(e) {
                @this.call('unselectProducts', e.params.data)
            });
            $('#select2-category-products').select2({
                placeholder: "Seluruh Kategori Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route('find.category_product') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                            ignore_access: 1,
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
                }
            });

            $('#select2-category-products').on('select2:select', function(e) {
                @this.call('selectCategoryProducts', e.params.data)
            });

            $('#select2-category-products').on('select2:unselect', function(e) {
                @this.call('unselectCategoryProducts', e.params.data)
            });
            $('#select2-supplier').select2({
                placeholder: "Seluruh Supplier",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route('find.supplier') }}",
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
                }
            });

            $('#select2-supplier').on('select2:select', function(e) {
                @this.call('selectSuppliers', e.params.data)
            });

            $('#select2-supplier').on('select2:unselect', function(e) {
                @this.call('unselectSuppliers', e.params.data)
            });
        })
    </script>
@endpush
