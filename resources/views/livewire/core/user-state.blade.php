@if ($arrangement == 'horizontal')
    <div class='row'>
        <div class="col-auto d-flex align-items-center mt-4">
            <label class='fw-bold me-2'>Perusahaan</label>
            <select class='form-select form-select-sm' wire:model.live='companyId'>
                @foreach ($companies as $company)
                    <option value="{{ $company['id'] }}">
                        {{ $company['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto d-flex align-items-center mt-4">
            <label class='fw-bold me-2'>Gudang</label>
            <select class='form-select form-select-sm' wire:model.live='warehouseId '>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse['id'] }}">
                        {{ $warehouse['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@else
    <div class='px-5 d-lg-none'>
        <div class='fw-bold'>Perusahaan</div>
        <select class='form-select form-select-sm' wire:model.live='companyId'>
            @foreach ($companies as $company)
                <option value="{{ $company['id'] }}">
                    {{ $company['name'] }}
                </option>
            @endforeach
        </select>

        <div class='fw-bold mt-4 mb-2'>Gudang</div>
        <select class='form-select form-select-sm' wire:model.live='warehouseId '>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse['id'] }}">
                    {{ $warehouse['name'] }}
                </option>
            @endforeach
        </select>
    </div>
@endif
