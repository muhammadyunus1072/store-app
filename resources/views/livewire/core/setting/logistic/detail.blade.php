<form wire:submit="store">
    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Detail Informasi Produk</h4>
            <hr>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="product_code" id='product_code'>
                <label class="form-check-label ms-2 mt-1" for='product_code'>
                    Input Kode Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="product_expired_date"
                    id='product_expired_date'>
                <label class="form-check-label ms-2 mt-1" for='product_expired_date'>
                    Input Tanggal Expired Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="product_attachment" id='product_attachment'>
                <label class="form-check-label ms-2 mt-1" for='product_attachment'>
                    Input Lampiran Setiap Produk
                </label>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model="product_batch" id='product_batch'>
                <label class="form-check-label ms-2 mt-1" for='product_batch'>
                    Input Batch Produk
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
            <select class="form-select" wire:model="product_substract_stock_method">
                @foreach ($product_substract_stock_method_choice as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Transaksi Pajak</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4" wire:ignore>
            <label>PPN Penerimaan Barang</label>
            <select class="form-select w-100" id="select-tax-ppn-good-receive-id">
                @if ($tax_ppn_good_receive_id)
                    <option value="{{ $tax_ppn_good_receive_id }}">{{ $tax_ppn_good_receive_text }}</option>
                @endif
            </select>
        </div>
    </div>

    <div class='row border rounded p-4 mb-4'>
        <div class="col-md-12 mb-4">
            <h4>Pengaturan Persetujuan</h4>
            <hr>
        </div>
        <div class="col-md-4 mb-4">
            <label>Kunci Persetujuan Permintaan </label>
            <input type="text" class="form-control" wire:model="approval_key_stock_request" />
        </div>
        <div class="col-md-4 mb-4">
            <label>Kunci Persetujuan Pengeluaran </label>
            <input type="text" class="form-control" wire:model="approval_key_stock_expense" />
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
            $('#select-tax-ppn-good-receive-id').select2({
                placeholder: "Pilih Pajak",
                ajax: {
                    url: "{{ route('setting_logistic.get.tax') }}",
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

            $('#select-tax-ppn-good-receive-id').on('select2:select', function(e) {
                @this.set('tax_ppn_good_receive_id', e.params.data.id);
            });
        })
    </script>
@endpush
