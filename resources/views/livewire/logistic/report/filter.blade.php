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
                <label>Gudang</label>
                <select class="form-select w-100" wire:model.live='warehouseId'>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                    @endforeach
                </select>
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
                    <button class="btn btn-danger ms-2" onclick="$('#select2-products').val('').trigger('change')">
                        Reset
                    </button>
                </div>
            </div>
        @endif

        {{-- SELECT CATEGORY PRODUCT --}}
        @if ($filterCategoryProductMultiple)
            <div class="col-md-4 mb-3" wire:ignore>
                <label>Kategori Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-category-products" multiple></select>
                    <button class="btn btn-danger ms-2"
                        onclick="$('#select2-category-products').val('').trigger('change')">
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
                    wire:click="$dispatch('export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_EXCEL }}' })">
                    <i class="fa fa-file-excel"></i>
                    Export Excel
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-light-danger btn-sm"
                    wire:click="$dispatch('export', { type: '{{ App\Helpers\General\ExportHelper::TYPE_PDF }}' })">
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
            // SELECT2 : PRODUCT
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route('logistic.report.find.product') }}",
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
                    url: "{{ route('logistic.report.find.product') }}",
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
                @this.call('onSelectProduct', e.params.data.id)
            });

            $('#select2-products').on('select2:unselect', function(e) {
                @this.call('onUnselectProduct', e.params.data.id)
            });

            // SELECT2: CATEGORY PRODUCT MULTIPLE
            $('#select2-category-products').select2({
                placeholder: "Pilih Kategori Produk",
                theme: 'bootstrap5',
                ajax: {
                    url: "{{ route('logistic.report.find.category_product') }}",
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
                @this.call('onSelectCategoryProduct', e.params.data.id)
            });

            $('#select2-category-products').on('select2:unselect', function(e) {
                @this.call('onUnselectCategoryProduct', e.params.data.id)
            });
        })
    </script>
@endpush
