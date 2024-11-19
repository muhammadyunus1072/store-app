<div class='w-100 pt-4'>
    {{-- FILTER TRANSACTION DATE --}}
    <div class="row">
        @if ($filterDateStart)
            <div class="col-md-3 mb-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" wire:model.live="dateStart" />
            </div>
        @endif
        @if ($filterDateEnd)
            <div class="col-md-3 mb-3">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" class="form-control" wire:model.live="dateEnd" />
            </div>
        @endif
    </div>

    <div class="row">
        {{-- SELECT COMPANY --}}
        @if ($filterCompany)
            <div class="col-md-3 mb-3">
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
            <div class="col-md-3 mb-3">
                <label>Gudang</label>
                <select class="form-select w-100" wire:model.live='warehouseId'>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- SELECT PRODUCT MULTIPLE --}}
        @if ($filterProductMultiple)
            <div class="col-md-3 mb-3" wire:ignore>
                <label>Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-products" multiple></select>
                </div>
            </div>
        @endif

        {{-- SELECT CATEGORY PRODUCT --}}
        @if ($filterCategoryProductMultiple)
            <div class="col-md-3 mb-3" wire:ignore>
                <label>Kategori Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-category-products" multiple></select>
                </div>
            </div>
        @endif

        {{-- SELECT SUPPLIER --}}
        @if ($filterSupplierMultiple)
            <div class="col-md-3 mb-3" wire:ignore>
                <label>Supplier</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-suppliers" multiple></select>
                </div>
            </div>
        @endif
    </div>
</div>

@push('js')
    <script>
        $(() => {
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

            // SELECT2: SUPPLIER MULTIPLE
            $('#select2-suppliers').select2({
                placeholder: "Pilih Supplier",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route($prefixRoute . 'find.supplier') }}",
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

            $('#select2-suppliers').on('select2:select', function(e) {
                @this.call('onSelect2Selected', 'supplierIds', e.params.data.id)
            });

            $('#select2-suppliers').on('select2:unselect', function(e) {
                @this.call('onSelect2Unselected', 'supplierIds', e.params.data.id)
            });
        })
    </script>
@endpush
