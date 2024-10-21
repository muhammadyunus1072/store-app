<div class='px-5'>
    <div class='{{ $isMultipleCompany ? '' : 'd-none' }}'>
        <div class="fw-bold">Perusahaan</div>
        <select class='form-select form-select-sm' wire:model.live='companyId'>
            @foreach ($companies as $company)
                <option value="{{ $company['id'] }}">
                    {{ $company['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class='fw-bold mt-4 mb-2'>Gudang</div>
    <select class='form-select form-select-sm' wire:model.live='warehouseId'>
        @foreach ($warehouses as $warehouse)
            <option value="{{ $warehouse['id'] }}">
                {{ $warehouse['name'] }}
            </option>
        @endforeach
    </select>
</div>