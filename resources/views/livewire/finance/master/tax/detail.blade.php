<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-4">
            <label>Nama Pajak</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.blur="name" />

            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-4 mb-4">
            <label>Tipe Pajak</label>
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
            <label>Nilai Persen Pajak</label>
            <input type="text" class="form-control currency @error('value') is-invalid @enderror" wire:model.blur="value" />

            @error('value')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-12 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="is_active">
                <label class="form-check-label ms-2 mt-1">
                    Status Aktif
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
