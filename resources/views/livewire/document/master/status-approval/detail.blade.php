<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-2 mb-4">
            <label>Warna Background</label>
            <input type="color" class="form-control form-control-color w-100 @error('color') is-invalid @enderror"
                wire:model="color" />

            @error('color')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-2 mb-4">
            <label>Warna Tulisan</label>
            <input type="color" class="form-control form-control-color w-100 @error('textColor') is-invalid @enderror"
                wire:model="textColor" />

            @error('textColor')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-auto mb-4 row align-items-end">
            <div class="form-check m-2">
                <input class="form-check-input" type="checkbox" wire:model="isTriggerDone">
                <label class="form-label ms-2 mb-2">
                    Penanda Selesai
                </label>
            </div>
        </div>
        <div class="col-md-auto mb-4 row align-items-end">
            <div class="form-check m-2">
                <input class="form-check-input" type="checkbox" wire:model="isTriggerCancel">
                <label class="form-label ms-2 mb-2">
                    Penanda Batal
                </label>
            </div>
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
