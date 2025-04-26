<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-4">
            <label>Nama Produk</label>
            <input type="text" placeholder="Nama Produk" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4">
            <label>Tipe Produk</label>
            <select class="form-select @error('type') is-invalid @enderror" wire:model.blur="type">
                @foreach ($type_choice as $key => $val)
                    <option value="{{ $key }}">{{ $val }}</option>
                @endforeach
            </select>

            @error('type')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-12 mb-4">
            <div class="w-100" wire:ignore>
                <label>Kategori Produk</label>

                <select id="select2-category_products" class="form-select" multiple>
                    @foreach ($category_products as $item)
                        <option value="{{ $item['id'] }}" selected>{{ $item['text'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <label>Stok Minimal</label>
            <input type="text" placeholder="Stok Minimal" class="form-control currency @error('min_stock') is-invalid @enderror" wire:model.blur="min_stock" />

            @error('min_stock')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-4 mb-4">
            <label>Stok Maksimal</label>
            <input type="text" placeholder="Stok Maksimal" class="form-control currency @error('max_stock') is-invalid @enderror" wire:model.blur="max_stock" />

            @error('max_stock')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="w-100" wire:ignore>
                <label>Satuan </label>

                <select id="select2-unit" class="form-select">
                    @if ($objId)
                        <option value="{{ $unit_id }}" selected>{{ $unit_title }}</option>
                    @endif
                </select>
            </div>
            <input type="hidden" class="form-control @error('unit_id') is-invalid @enderror" />
            @error('unit_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="table-responsive">
            <table class='table table-bordered text-nowrap w-100 h-100'>
                <thead>
                    <tr>
                        <th>
                            <div class="fs-6 p-2">Satuan</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Barcode</div>
                        </th>
                        <th>
                            <div class="fs-6 p-2">Harga Jual</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productUnits as $index => $product_unit)
                    
                        <tr>

                            {{-- NAME --}}
                            <td>
                                {{ $product_unit['text'] }}
                            </td>
    
                            {{-- CURRENT STOCK --}}
                            
                                <td>
                                    <div class="input-group" style="width:170px;">
                                        <input type="text" class="form-control" style="width:100%;"
                                            wire:model="productUnits.{{ $index }}.code" />
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group" style="width:170px;">
                                        <input type="text" class="form-control currency" style="width:100%;"
                                            wire:model="productUnits.{{ $index }}.selling_price" />
                                    </div>
                                </td>
                            
    
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan
    </button>
</form>

@include('js.imask')

@push('js')
    <script>
        $(() => {
            // Select2 Unit
            $('#select2-unit').select2({
                placeholder: "Pilih Satuan",
                ajax: {
                    url: "{{ route('product.get.unit') }}",
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

            $('#select2-unit').on('change', async function(e) {
                var data = $('#select2-unit').select2("val");
                @this.set('unit_id', data);
            });

            // Select2 CategoryProduct
            $('#select2-category_products').select2({
                placeholder: "Pilih Kategori Produk",
                ajax: {
                    url: "{{ route('product.get.category_product') }}",
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

            $('#select2-category_products').on('select2:select', function(e) {
                @this.call('selectCategoryProduct', e.params.data)
            });

            $('#select2-category_products').on('select2:unselect', function(e) {
                @this.call('unselectCategoryProduct', e.params.data)
            });
        })
    </script>
@endpush
