<form wire:submit="store">
    <div class='row'>
        <div class="col-md-6 mb-4">
            <label>Nama</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" wire:model.blur="nama" />

            @error('nama')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-success mt-3">
        <i class='ki-duotone ki-check fs-1'></i>
        Simpan
    </button>
</form>
