<form wire:submit="store">
    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Global Sistem</h4>
            <hr>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="setting.{{ SettingCore::MULTIPLE_COMPANY }}"
                    id='setting.{{ SettingCore::MULTIPLE_COMPANY }}' {{ $isMultipleCompanyDisabled ? 'disabled' : '' }}>
                <label class="form-check-label ms-2 mt-1" for='setting.{{ SettingCore::MULTIPLE_COMPANY }}'>
                    Banyak Perusahaan
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
