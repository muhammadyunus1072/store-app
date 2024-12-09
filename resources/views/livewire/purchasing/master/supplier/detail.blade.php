<form wire:submit="store">
    <div class='row'>
        <div class="col-md-6 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6 mb-4">
            <label>Kode Simrs</label>
            <input type="text" class="form-control @error('kode_simrs') is-invalid @enderror" wire:model.blur="kode_simrs" />

            @error('kode_simrs')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-12 mb-4">
            <div class="w-100" wire:ignore>
                <label>Kategori Supplier</label>

                <select id="select2-category_suppliers" class="form-select" multiple>
                    @foreach ($supplierCategories as $item)
                        <option value="{{$item['id']}}" selected>{{$item['text']}}</option>
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

@push('js')
    <script>
        $(() => {
            $('#select2-category_suppliers').select2({
                placeholder: "Pilih Kategori Supplier",
                ajax: {
                    url: "{{ route('supplier.get.category_supplier') }}",
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

            $('#select2-category_suppliers').on('select2:select', function(e) {
                @this.call('selectCategorySupplier', e.params.data)
            });

            $('#select2-category_suppliers').on('select2:unselect', function(e) {
                @this.call('unselectCategorySupplier', e.params.data)
            });
        })
    </script>
@endpush
