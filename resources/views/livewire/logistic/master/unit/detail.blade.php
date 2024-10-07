<form wire:submit="store">
    <div class='row'>
        <div class="col-md-6 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model.blur="title" />

            @error('title')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <div class="row">
        {{-- INVOICE DETAIL --}}
        <div class="mb-3">
            <label>Satuan - Detail</label>

            <button type="button" class="btn btn-primary btn-sm" wire:click="addDetail">
                <i class='fa fa-plus'></i>
                Tambah
            </button>

            @foreach ($unitDetails as $index => $item)
                <div class="row my-2" wire:key="{{ $item['key'] }}">
                    <div class="col-auto d-flex">
                        <button type="button" class="btn btn-danger mx-auto align-self-end h-auto"
                            wire:click="removeDetail({{ $index }})">
                            <i class='fa fa-times'></i>
                        </button>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Satuan</label>
                        @endif
                        <input type="text" class="form-control"
                            wire:model="unitDetails.{{ $index }}.name" />
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Nilai Konversi</label>
                        @endif
                        <input type="text" class="form-control currency"
                            wire:model="unitDetails.{{ $index }}.value" {{ $unitDetails[$index]['is_main'] ? 'disabled' : ''}}/>
                    </div>
                    <div class="col d-flex">
                        <div class="form-check align-self-end mb-2">
                            <input class="form-check-input" type="checkbox" wire:model.live="unitDetails.{{ $index }}.is_main"/>
                            <label class="form-check-label" for="flexCheckDefault">
                                Satuan Utama
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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
