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
            <label>ID Sub</label>
            <input type="text" class="form-control @error('id_sub') is-invalid @enderror" wire:model.blur="id_sub" />

            @error('id_sub')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6 mb-4">
            <label>ID Bagian</label>
            <input type="text" class="form-control @error('id_bagian') is-invalid @enderror" wire:model.blur="id_bagian" />

            @error('id_bagian')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-md-6 mb-4">
            <label>ID Direktorat</label>
            <input type="text" class="form-control @error('id_direktorat') is-invalid @enderror" wire:model.blur="id_direktorat" />

            @error('id_direktorat')
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
