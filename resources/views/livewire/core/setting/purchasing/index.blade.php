<form wire:submit="store">
    <div class='row border rounded align-items-center p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Pembelian</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4" wire:ignore>
            <label>PPN Barang</label>
            <select class="form-select w-100" id="select2-{{ SettingPurchasing::TAX_PPN_ID }}">
                @if ($setting[SettingPurchasing::TAX_PPN_ID])
                    <option value="{{ $setting[SettingPurchasing::TAX_PPN_ID] }}">
                        {{ $setting[SettingPurchasing::TAX_PPN_ID . '_text'] }}
                    </option>
                @endif
            </select>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK }}"
                    id="setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK }}">
                <label class="form-check-label ms-2 mt-1"
                    for="setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK }}">
                    Pembelian Langsung Penambahan Stok
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN }}"
                    id='setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN }}'>
                    Nilai Stok Ditambahkan Pajak PPN
                </label>
            </div>
        </div>

        <div class="col-md-12 mt-4 mb-4">
            <h5>Detail Input Barang</h5>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE }}"
                    id='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE }}'>
                    Input Kode Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE }}"
                    id='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE }}'>
                    Input Tanggal Expired Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_ATTACHMENT }}"
                    id='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_ATTACHMENT }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_ATTACHMENT }}'>
                    Input Lampiran Setiap Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model="setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH }}"
                    id='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH }}'>
                <label class="form-check-label ms-2 mt-1"
                    for='setting.{{ SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH }}'>
                    Input Batch Produk
                </label>
            </div>
        </div>
    </div>

    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Persetujuan</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4">
            <label>Kunci Persetujuan Pembelian</label>
            <input type="text" class="form-control"
                wire:model="setting.{{ SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER }}" />
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


@push('js')
    <script>
        $(() => {
            $('#select2-{{ SettingPurchasing::TAX_PPN_ID }}').select2({
                placeholder: "Pilih Pajak",
                ajax: {
                    url: "{{ route('setting_purchasing.get.tax') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    "id": item.id,
                                    "text": item.text,
                                }
                            })
                        };
                    },
                },
                cache: true
            });

            $('#select2-{{ SettingPurchasing::TAX_PPN_ID }}').on('select2:select', function(e) {
                @this.set('setting.{{ SettingPurchasing::TAX_PPN_ID }}', e.params.data.id);
            });
        })
    </script>
@endpush
