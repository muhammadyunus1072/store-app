<div class='row w-100 pt-3'>
    <div class="row">
        <div class="row">
            @if ($show_input_date_start)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" wire:model.live="date_start" />
                </div>
            @endif
            @if ($show_input_date_end)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" wire:model.live="date_end" />
                </div>
            @endif
        </div>
        <div class="row">
            @if ($show_input_entry_date_start)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tgl Masuk Mulai</label>
                    <div class="d-flex">
                        <input type="date" class="form-control" wire:model.live="entry_date_start" />
                        <button class="btn btn-danger ms-2" wire:click="resetEntryDateStart">
                            Reset
                        </button>
                    </div>
                </div>
            @endif
            @if ($show_input_entry_date_end)
                <div class="col-md-4 mb-2">
                    <label class="form-label">Tgl Masuk Akhir</label>
                    <div class="d-flex">
                        <input type="date" class="form-control" wire:model.live="entry_date_end" />
                        <button class="btn btn-danger ms-2" wire:click="resetEntryDateEnd">
                            Reset
                        </button>
                    </div>
                </div>
            @endif
        </div>
        @if ($show_input_product)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-products" multiple></select>
                </div>
            </div>
        @endif
        @if ($show_input_category_product)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Kategori Produk</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-category-products" multiple></select>
                </div>
            </div>
        @endif
        @if ($show_input_warehouse)

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
        @if ($show_input_supplier)
            <div class="col-md-4 mb-2" wire:ignore>
                <label>Suplier</label>
                <div class="d-flex">
                    <select class="form-select" id="select2-supplier"></select>
                    <button class="btn btn-danger ms-2" onclick="$('#select2-supplier').val('').trigger('change')">
                        Reset
                    </button>
                </div>
            </div>
        @endif
    </div>
    <div class="row">
        @if ($show_input_expired_date_start && $setting_product_expired_date)
            <div class="col-md-4 mb-2">
                <label class="form-label">Expired Date Mulai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expired_date_start" />
                    <button class="btn btn-danger ms-2" wire:click="resetExpiredDateStart">
                        Reset
                    </button>
                </div>
            </div>
        @endif
        @if ($show_input_expired_date_end && $setting_product_expired_date)
            <div class="col-md-4 mb-2">
                <label class="form-label">Expired Date Sampai</label>
                <div class="d-flex">
                    <input type="date" class="form-control" wire:model.live="expired_date_end" />
                    <button class="btn btn-danger ms-2" wire:click="resetExpiredDateEnd">
                        Reset
                    </button>
                </div>
            </div>
        @endif
    </div>
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
        })
    </script>
@endpush
