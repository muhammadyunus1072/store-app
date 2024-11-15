<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-4">
            <label>Kunci</label>
            <input type="text" class="form-control @error('key') is-invalid @enderror" wire:model="key" />

            @error('key')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4">
            <label>Prioritas</label>
            <input type="text" class="form-control currency @error('priority') is-invalid @enderror"
                wire:model="priority" />

            @error('priority')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4 row align-items-end">
            <div class="form-check m-2">
                <input class="form-check-input" type="checkbox" wire:model="isSequentially">
                <label class="form-label ms-2 mb-2">
                    Persetujuan Harus Berurutan
                </label>
            </div>
        </div>
    </div>

    {{-- CONFIG RULES --}}
    <hr>
    <div class="row my-4">
        <div class="col-md-12 mb-3">
            <label class='fs-4 fw-bold'>List Aturan</label>
            <button type="button" class="btn btn-primary btn-sm" wire:click="addConfig()">
                <i class='fa fa-plus'></i>
                Tambah Aturan
            </button>
        </div>
    </div>
    <div class="mt-3">
        @foreach ($config as $index => $configItem)
            @include('app.document.master.approval-config.config-item', [
                'configItem' => $configItem,
                'index' => $index,
                'key' => [],
            ])
        @endforeach
    </div>

    {{-- CONFIG USER --}}
    <hr>
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class='fs-4 fw-bold'>List Pengguna Penyetuju</label>
        </div>

        <div class="col-md-12 mb-3">
            <div class="w-100" wire:ignore>
                <select id="select2-user" class="form-select"></select>
            </div>
        </div>

        @foreach ($approvalConfigUsers as $index => $item)
            <div class="row align-items-end my-2" wire:key="{{ $item['key'] }}">
                <div class="col-auto mb-2">
                    <button type="button" class="btn btn-danger btn-sm"
                        wire:click="removeApprover({{ $index }})">
                        <i class='fa fa-times'></i>
                    </button>
                </div>
                <div class="col-md-4 mb-2">
                    <label class='fw-bold'>Nama</label>
                    <input class="form-control" value='{{ $approvalConfigUsers[$index]['user_text'] }}' disabled>
                </div>
                <div class="col-md-2 mb-2">
                    <label class='fw-bold'>Posisi</label>
                    <input type="text" class="form-control currency"
                        wire:model.live="approvalConfigUsers.{{ $index }}.position" min="1" />
                </div>
                <div class="col-md-auto mb-2">
                    <div class="form-check m-2">
                        <input class="form-check-input" type="checkbox"
                            wire:model="approvalConfigUsers.{{ $index }}.is_trigger_done">
                        <label class="form-label ms-2 mb-2">
                            Persetujuan Selesai
                        </label>
                    </div>
                </div>
                <div class="col-md-auto mb-2">
                    <div class="form-check m-2">
                        <input class="form-check-input" type="checkbox"
                            wire:model="approvalConfigUsers.{{ $index }}.is_can_cancel">
                        <label class="form-label ms-2 mb-2">
                            Dapat Membatalkan
                        </label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan
    </button>
</form>

@push('css')
    <style>
        input[type=checkbox] {
            /* Double-sized Checkboxes */
            -ms-transform: scale(1.2);
            /* IE */
            -moz-transform: scale(1.2);
            /* FF */
            -webkit-transform: scale(1.2);
            /* Safari and Chrome */
            -o-transform: scale(1.2);
            /* Opera */
            padding: 8px;
        }
    </style>
@endpush

@include('js.imask')

@push('js')
    <script>
        $(document).ready(function() {
            initSelect2();
        });

        function initSelect2() {
            // Select2 User
            $('#select2-user').select2({
                placeholder: "Pilih Pengguna Menyetujui",
                ajax: {
                    url: "{{ route('approval_config.get.user') }}",
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

            $('#select2-user').on('select2:select', function(e) {
                var selectedOption = e.params.data;

                if (selectedOption) {
                    @this.call('addApprover', selectedOption);
                    $('#select2-user').val('').trigger('change');
                }
            });
        }
    </script>
@endpush
