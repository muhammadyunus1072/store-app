<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-4">
            <label>Nama Produk</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4">
            <label>Kode SIMRS</label>
            <input type="text" class="form-control @error('kode_simrs') is-invalid @enderror" wire:model.blur="kode_simrs" />

            @error('kode_simrs')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4">
            <label>Kode SAKTI</label>
            <input type="text" class="form-control @error('kode_sakti') is-invalid @enderror" wire:model.blur="kode_sakti" />

            @error('kode_sakti')
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

        <div class="col-md-4 mb-4">
            <label class='fw-bold'>Persentasi TKDN</label>
            <input type="text" class="form-control currency @error('interkoneksi_sakti_persentase_tkdn') is-invalid @enderror" wire:model.blur="interkoneksi_sakti_persentase_tkdn" />
        </div>
        <div class="col-md-4 mb-4">
            <label class='fw-bold'>Kategori TKDN</label>
            <input type="text" class="form-control @error('interkoneksi_sakti_kategori_pdn') is-invalid @enderror" wire:model.blur="interkoneksi_sakti_kategori_pdn" />
        </div>

        <div class="col-md-4 mb-4">
            <label class='fw-bold'>KBKI</label>
            <select class="form-select" wire:model="interkoneksi_sakti_kbki_id">
                @foreach ($interkoneksi_sakti_kbki_id_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-4">
            <label class='fw-bold'>COA</label>
            <select class="form-select" wire:model="interkoneksi_sakti_coa_id">
                @foreach ($interkoneksi_sakti_coa_id_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
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
