<div class='row w-100 mt-3'>
    <div class="col-md-3 mb-4">
        <label>Jabatan</label>
        <select class="form-select d-block" wire:model.live="role">
            <option>Seluruh</option>
            @foreach ($roles as $role)
                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
            @endforeach
        </select>
    </div>
</div>
