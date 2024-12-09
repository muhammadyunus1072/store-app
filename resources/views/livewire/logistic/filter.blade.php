<div class='w-100 pt-4'>
    {{-- FILTER TRANSACTION DATE --}}
    <div class="row">
        @if ($filterDateStart)
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" wire:model.live="dateStart" />
            </div>
        @endif
        @if ($filterDateEnd)
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" wire:model.live="dateEnd" />
            </div>
        @endif
    </div>

    {{-- FILTER ENTRY DATE --}}
    <div class="row">
        @if ($filterEntryDateStart)
            <div class="col-md-4 mb-3">
                <label class="form-label">Tgl Masuk Mulai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="entryDateStart" />
                    <button class="btn btn-danger ms-2" wire:click="$set('entryDateStart', null)">
                        Reset
                    </button>
                </div>
            </div>
        @endif
        @if ($filterEntryDateEnd)
            <div class="col-md-4 mb-3">
                <label class="form-label">Tgl Masuk Akhir</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="entryDateEnd" />
                    <button class="btn btn-danger ms-2" wire:click="$set('entryDateEnd', null)">
                        Reset
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- FILTER EXPIRED DATE --}}
    <div class="row">
        @if ($filterExpiredDateStart)
            <div class="col-md-4 mb-3">
                <label class="form-label">Expired Date Mulai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expiredDateStart" />
                    <button class="btn btn-danger ms-2" wire:click="$set('expiredDateStart', null)">
                        Reset
                    </button>
                </div>
            </div>
        @endif
        @if ($filterExpiredDateEnd)
            <div class="col-md-4 mb-3">
                <label class="form-label">Expired Date Sampai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expiredDateEnd" />
                    <button class="btn btn-danger ms-2" wire:click="$set('expiredDateEnd', null)">
                        Reset
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div class="row">
        {{-- SELECT COMPANY --}}
        @if ($filterCompany)
            <div class="col-md-4 mb-3">
                <label>Perusahaan</label>
                <select class="form-select w-100" wire:model.live='companyId'>
                    @foreach ($companies as $company)
                        <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- SELECT WAREHOUSE --}}
        @if ($filterWarehouse)
            <div class="col-md-4 mb-3">
                <label>{{ $filterWarehouseLabel }}</label>
                <select class="form-select w-100" wire:model.live='warehouseId'>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- SELECT WAREHOUSE MULTIPLE --}}
        @if ($filterWarehouseMultiple)
            <div class="col-md-4 mb-3">
                <label>{{ $filterWarehouseMultipleLabel }}</label>
                <div class="d-flex">
                    <select class="form-select w-100" id='select2-warehouses'>
                    </select>
                    <button class="btn btn-danger ms-2" onclick="$('#select2-warehouses').val('').trigger('change')">
                        Reset
                    </button>
                </div>
            </div>
        @endif

        {{-- SELECT PRODUCT --}}
        @if ($filterProduct)
            <div class="col-md-4 mb-3" wire:ignore>
                <label>Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-product"></select>
                    <button class="btn btn-danger ms-2" onclick="$('#select2-product').val('').trigger('change')">
                        Reset
                    </button>
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

        {{-- SELECT CATEGORY PRODUCT --}}
        @if ($filterCategoryProductMultiple)
            <div class="col-md-4 mb-3" wire:ignore>
                <label>Kategori Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-category-products" multiple></select>
                </div>
            </div>
        @endif
    </div>
</div>

@push('js')
    <script>
        $(() => {
            // SELECT2 : PRODUCT
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route($prefixRoute . 'find.product') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                            product_stock: 1,
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

            $('#select2-product').on('change', async function(e) {
                @this.set('productId', $('#select2-product').val());
            });

            // SELECT2 : PRODUCT MULTIPLE
            $('#select2-products').select2({
                placeholder: "Pilih Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route($prefixRoute . 'find.product') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
                            product_stock: 1,
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
                @this.call('onSelect2Selected', 'productIds', e.params.data.id)
            });

            $('#select2-products').on('select2:unselect', function(e) {
                @this.call('onSelect2Unselected', 'productIds', e.params.data.id)
            });

            // SELECT2: CATEGORY PRODUCT MULTIPLE
            $('#select2-category-products').select2({
                placeholder: "Pilih Kategori Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route($prefixRoute . 'find.category_product') }}",
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

            $('#select2-category-products').on('select2:select', function(e) {
                @this.call('onSelect2Selected', 'categoryProductIds', e.params.data.id)
            });

            $('#select2-category-products').on('select2:unselect', function(e) {
                @this.call('onSelect2Unselected', 'categoryProductIds', e.params.data.id)
            });
        })

        // SELECT2 : WAREHOUSE MULTIPLE
        $('#select2-warehouses').select2({
            placeholder: "Pilih Gudang",
            theme: 'bootstrap5',
            ajax: {
                url: "{{ route($prefixRoute . 'find.warehouse') }}",
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

        $('#select2-warehouses').on('select2:select', function(e) {
            @this.call('onSelect2Selected', 'warehouseIds', e.params.data.id)
        });

        $('#select2-warehouses').on('select2:unselect', function(e) {
            @this.call('onSelect2Unselected', 'warehouseIds', e.params.data.id)
        });
    </script>
@endpush
