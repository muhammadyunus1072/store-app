<form wire:submit="store">
    <div class='row'>
        <div class="col-md-auto mt-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerateData" wire:click="showGenerateData" {{ $isGenerateProcess ? 'disabled' : '' }}>
                @if ($isGenerateProcess)
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading ({{ $isGenerateProcess->progress ? $isGenerateProcess->progress : 0 }} / {{ $isGenerateProcess->total }})
                @else
                    <i class="fa fa-sync"></i>
                    Generate Data
                @endif
            </button>
        </div>
    </div>

    {{-- MODAL UPDATE --}}
    <div class="modal fade" id="modalGenerateData" data-backdrop="static" data-keyboard="false" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Data</h5>
                    <button class="btn" type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveBulk">
                    <div class="modal-body">
                        
                        <div class='row mb-2' wire:ignore>
                            <label>Gudang</label>
                            <select id="select2-warehouse" class="form-select w-100">
                            </select>
                        </div>
                        <div class="row mb-2">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" wire:model="dateStart" />
                        </div>
                        <div class="row mb-2">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" wire:model="dateEnd" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Generate Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</form>

@push('js')
    <script>
        $(() => {
            // Select2 Supplier
            $('#select2-warehouse').select2({
                placeholder: "Pilih Gudang",
                dropdownParent: $('#modalGenerateData'),
                ajax: {
                    url: "{{ route('interkoneksi_sakti_detail_barang.get.warehouse') }}",
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

            $('#select2-warehouse').on('change', async function(e) {
                let data = $('#select2-warehouse').val();
                @this.set('warehouseId', data);
            });
        });
    </script>
@endpush
