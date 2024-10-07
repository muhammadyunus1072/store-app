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

        <div class="col-md-12 mb-4">
            <div class="w-100" wire:ignore>
                <label>Gudang</label>

                <select id="select2-warehouses" class="form-select" multiple>
                    @foreach ($companyWarehouses as $item)
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

@push('js')
    <script>
        $(() => {
            $('#select2-warehouses').select2({
                placeholder: "Pilih Gudang",
                ajax: {
                    url: "{{ route('company.get.warehouse') }}",
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

            $('#select2-warehouses').on('select2:select', function(e) {
                @this.call('selectWarehouse', e.params.data);
            });

            $('#select2-warehouses').on('select2:unselect', function(e) {
                @this.call('unselectWarehouse', e.params.data)
            });
        })
    </script>
@endpush
