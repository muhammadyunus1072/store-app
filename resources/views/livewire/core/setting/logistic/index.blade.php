<form wire:submit="store">
    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Detail Informasi Produk</h4>
            <hr>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingLogistic::INFO_PRODUCT_CODE }}"
                    id='setting.{{ SettingLogistic::INFO_PRODUCT_CODE }}'>
                <label class="form-check-label ms-2 mt-1" for='setting.{{ SettingLogistic::INFO_PRODUCT_CODE }}'>
                    Input Kode Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingLogistic::INFO_PRODUCT_EXPIRED_DATE }}"
                    id='setting.{{ SettingLogistic::INFO_PRODUCT_EXPIRED_DATE }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingLogistic::INFO_PRODUCT_EXPIRED_DATE }}'>
                    Input Tanggal Expired Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingLogistic::INFO_PRODUCT_ATTACHMENT }}"
                    id='setting.{{ SettingLogistic::INFO_PRODUCT_ATTACHMENT }}'>
                <label class="form-check-label ms-2 mt-1" for='setting.{{ SettingLogistic::INFO_PRODUCT_ATTACHMENT }}'>
                    Input Lampiran Setiap Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingLogistic::INFO_PRODUCT_BATCH }}"
                    id='setting.{{ SettingLogistic::INFO_PRODUCT_BATCH }}'>
                <label class="form-check-label ms-2 mt-1" for='setting.{{ SettingLogistic::INFO_PRODUCT_BATCH }}'>
                    Input Batch Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingLogistic::PRICE_INTEGER_VALUE }}"
                    id='setting.{{ SettingLogistic::PRICE_INTEGER_VALUE }}'>
                <label class="form-check-label ms-2 mt-1" for='setting.{{ SettingLogistic::PRICE_INTEGER_VALUE }}'>
                    Nilai Stok Bilangan Bulat
                </label>
            </div>
        </div>
    </div>

    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Metode Pengurangan Stok</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4">
            <label class='fw-bold'>Metode Pengurangan Stok</label>
            <select class="form-select" wire:model="setting.{{ SettingLogistic::SUBSTRACT_STOCK_METHOD }}">
                @foreach ($product_substract_stock_method_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Persetujuan</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4">
            <label>Kunci Persetujuan Permintaan</label>
            <input type="text" class="form-control" wire:model="setting.{{ SettingLogistic::APPROVAL_KEY_STOCK_REQUEST }}" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Kunci Persetujuan Pengeluaran </label>
            <input type="text" class="form-control" wire:model="setting.{{ SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE }}" />
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
