<form wire:submit="store">
    <div class="row w-100">
        <div class="col-md-8">
            <div class="row" style="height: 170px;">
                <div class="col-md-7 p-2 d-flex justify-content-center align-items-center row">
                    <div class="col-auto">
                        <h1 class="display-5">Rp. @currency($grand_total)</h1>
                    </div>
                </div>
                <div class="col-md-5 border">
                    <div class="row">
                        <div class="col mb-4 mt-3" wire:ignore>
                            <select id="select2-product" class="form-select">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="table-responsive p-0">
                    <table class='table table-bordered text-nowrap w-100 h-100'>
                        <thead>
                            <tr>
                                <th>
                                    <div class="fs-6 p-2">KODE</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">Deskripsi</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">QTY</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">Satuan</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">Harga</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">Subtotal</div>
                                </th>
                                <th>
                                    <div class="fs-6 p-2">Aksi</div>
                                </th>
                            </tr>
                        </thead>

                        <tbody  x-data="{
                            products: @entangle('transactionDetails'),
                            get grand_total() {
                                return this.products.reduce((sum, product) => {
                                    const price = product.choice[product.unit_detail_id]?.unit_selling_price ?? 0;
                                    return sum + (product.qty * price);
                                }, 0);
                            }
                        }">
                            <template x-for="(product, index) in products" :key="product.key">
                                <tr>
                                    <td class="my-0 py-0" x-text="product.plu"></td>
                                    <td class="my-0 py-0" x-text="product.name"></td>
                                    <td class="my-0 py-0">
                                        <input type="text" placeholder="QTY" class="form-control currency py-1"
                                            x-model.number="product.qty" 
                                            @keyDown="$wire.calculateGrandTotal()" />
                                    </td>
                                    <td class="my-0 py-0">
                                        <select class="style-select w-100 py-1" x-model="product.unit_detail_id" style="min-width: 170px;" @change="$wire.calculateGrandTotal()">
                                            <template x-for="(choice, unitId) in product.choice" :key="unitId">
                                                <option :value="unitId" x-text="choice.is_main ? choice.unit_name : `${choice.unit_name} (${currencyFormat(choice.unit_value)} ${product.main_unit.unit_name})`"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="my-0 py-0" x-text="currencyFormat(product.choice[product.unit_detail_id]?.unit_selling_price ?? 0)"></td>
                                    <td class="my-0 py-0" x-text="currencyFormat(product.qty * (product.choice[product.unit_detail_id]?.unit_selling_price ?? 0))"></td>

                                    <td class="my-0 py-0">
                                        <button type="button" class="btn btn-sm btn-danger m-0" wire:click="removeProduct(index)">
                                            <i class="ki-solid ki-abstract-11"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody>
                            <tr wire:key="input">
                                <td colspan="7" class="pb-0">
                                    <input type="text" placeholder="TAB / KLIK Untuk SCAN" class="form-control @error('input') is-invalid @enderror py-1" wire:model.live="input" id="input-scan" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row" style="height: 170px;">
                <div class="col-md-12 d-flex justify-content-center align-items-center row">
                    <div class="col-auto">
                        <h1 class="text-center">Product Terakhir</h1>
                        @if ($last_item)
                            {{$last_item['name']}} 
                            <p>Rp. @currency($last_item['unit_selling_price']) / {{$last_item['unit_name']}}</p>
                        @else
                            <h3 class="text-danger text-center">Belum Ada Produk</h3>
                        @endif
                    </div>
                </div>
                {{-- <div class="col-md-4"></div>
                <div class="col-md-4"></div> --}}
            </div>
            <div class="row mt-3 p-3">
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        Bayar
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-warning w-100">
                        Tunda
                    </button>
                </div>
                <div class="col-md-6"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="paymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="paymentModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Pembayaran</h5>
                    <button type="button" class="close btn" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <h1 class="display-5">Total Rp. @currency($grand_total)</h1>
                    </div>
                    <div class="row g-3 align-items-center w-50 mx-auto">
                        <div class="col-auto">
                            <label for="cash" class="col-form-label">Cash</label>
                        </div>
                        <div class="col">
                            <input id="cash" type="text" placeholder="Cash" class="form-control fs-1 currency @error('cash') is-invalid @enderror" wire:model.live="cash" />
                        </div>
                    </div>

                    <div class="row w-100 mx-auto mt-3">
                        <div class="col-md-7 row">
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(100)">
                                    100
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(500)">
                                    500
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(1000)">
                                    1K
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(5000)">
                                    5K
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(10000)">
                                    10K
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(20000)">
                                    20K
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(50000)">
                                    50K
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-light fs-1 w-100" wire:click="handleAddPayment(100000)">
                                    100K
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5" style="padding: 50px;">
                            <div class="row mb-2">
                                <button type="button" class="btn btn-success fs-1 w-100" wire:click="handleExactPayment">
                                    UANG PAS
                                </button>
                            </div>
                            <div class="row mb-2">
                                <button type="button" class="btn btn-danger fs-1 w-100" wire:click="handleDeletePayment">
                                    HAPUS
                                </button>
                            </div>
                            <div class="row mb-2">
                                <button type="button" class="btn btn-warning fs-1 w-100" wire:click="handleDeletePayment" data-bs-dismiss="modal" aria-label="Close">
                                    KEMBALI
                                </button>
                            </div>
                            <div class="row mb-2">
                                <button type="button" class="btn btn-primary fs-1 w-100" wire:click="handleSavePayment">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('js.imask')

@push('css')
    <style>
        #input-scan:focus{
            border: 2px solid rgb(55, 186, 103);
        }
        .style-select {
            /* --bs-form-select-bg-img: url(data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2378829D' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e); */
            display: block;
            width: 100%;
            padding: .775rem 1rem .775rem 1rem;
            font-size: 1.1rem;
            font-weight: 500;
            line-height: 1.5;
            color: var(--bs-gray-700);
            background-color: var(--bs-body-bg);
            /* background-image: var(--bs-form-select-bg-img), var(--bs-form-select-bg-icon, none); */
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            border: 1px solid var(--bs-gray-300);
            border-radius: .475rem;
            box-shadow: false;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            appearance: none;
        }
    </style>
@endpush

@push('js')
    <script>
        
        function currencyFormat(value) {
            return new Intl.NumberFormat('id-ID').format(value);
        }
        function yourFunction(key) {
            alert('You pressed! '+key);
            // Or do anything you want, like focusing an input or saving a transaction
        }
        function closeSelect2() {
            $('#select2-product').select2('close');
        };
        $(document).ready(function() {
            initSelect2();
            document.addEventListener('keydown', function(event) {
            if (event.key === 'Tab') {
                event.preventDefault();
                
                $('#select2-product').select2('close');
                $("#input-scan").focus();
            }
            if (event.ctrlKey && event.key === 's') {
                event.preventDefault();
                
                $('#select2-product').select2('open');
            }
            if (event.ctrlKey && event.key === 't') {
                event.preventDefault();
                
                $('#select2-product').select2('close');
            }
        });
        });

        function initSelect2() {
            // Select2 Product
            $('#select2-product').select2({
                placeholder: "Cari Produk",
                minimumInputLength: 1,
                ajax: {
                    url: "{{ route('cashier_transaction.get.product') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            search: params.term,
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

            $('#select2-product').on('select2:select', function(e) {
                let data = e.params.data;
                if (data) {
                    @this.call('addProduct', data);
                    $('#select2-product').val('').trigger('change');
                }
                setTimeout(() => {
                    $('#select2-product').select2('open');
                }, 200);
            });
        }
    </script>
@endpush
