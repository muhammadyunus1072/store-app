<form wire:submit="store">
    <div class='row'>
        <div class="col-md-4 mb-3">
            <label>Supplier</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-supplier" class="form-select">
                        @if ($objId)
                            <option value="{{$supplier_id}}">{{$supplier_text}}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <label>Tanggal Pembelian</label>
            <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" wire:model="purchase_date" />

            @error('purchase_date')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <label>Catatan</label>
            <textarea class="form-control" cols="30" rows="4" wire:model="note"></textarea>
        </div>
    </div>

    <div class="row">
        {{-- INVOICE DETAIL --}}
        <div class="col-md-12 mb-3">
            <label>Data Pembelian</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-product" class="form-select">
                    </select>
                </div>
            </div>

            @foreach ($purchaseOrderProducts as $index => $item)
                <div class="row my-2" wire:key="{{ $item['key'] }}">
                    <div class="col-auto d-flex mb-3">
                        <button type="button" class="btn btn-danger mx-auto align-self-end h-auto"
                            wire:click="removeDetail({{ $index }})">
                            <i class='fa fa-times'></i>
                        </button>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Produk</label>
                        @endif
                        <p class="form-control">{{$purchaseOrderProducts[$index]['product_text']}}</p>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Satuan</label>
                        @endif
                        
                        <select class="form-select @error('type') is-invalid @enderror" wire:model.blur="purchaseOrderProducts.{{ $index }}.unit_detail_id">
                            @foreach ($purchaseOrderProducts[$index]['unit_detail_choice'] as $item)
                                <option value="{{ $item['enc_id'] }}">{{ $item['name'] }} ({{$item['value']}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        @if (!$index)
                            <label class='fw-bold'>Jumlah Dibeli</label>
                        @endif
                        <input type="text" class="form-control currency"
                            wire:model="purchaseOrderProducts.{{ $index }}.quantity" />
                    </div>
                    <div class="col">
                        @if (!$index)
                            <label class='fw-bold'>Harga</label>
                        @endif
                        <input type="text" class="form-control currency"
                            wire:model="purchaseOrderProducts.{{ $index }}.price" />
                    </div>
                    <div class="col d-flex flex-column">
                        @if (!$index)
                            <label class='fw-bold'>Pajak</label>
                        @endif
                        <div class="row d-flex mt-3">
                            <input class="form-check-input mx-2" type="checkbox"
                            wire:model="purchaseOrderProducts.{{ $index }}.is_ppn">
                            <label class="form-check-label w-auto">
                                PPN
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
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

@include('js.imask')

@push('js')
    <script>

        $(document).ready(function() {
            initSelect2();
        });

        function initSelect2()
        {
            // Select2 Supplier
            $('#select2-supplier').select2({
                placeholder: "Pilih Supplier",
                ajax: {
                    url: "{{ route('purchase_order.get.supplier') }}",
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

            $('#select2-supplier').on('change', async function(e) {
                var data = $('#select2-supplier').select2("val");
                @this.set('supplier_id', data);
            });

            // Select2 Product
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                ajax: {
                    url: "{{ route('purchase_request.get.product') }}",
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

            $('#select2-product').on('select2:select', function (e) {
                // Triggered when an option is selected
                var selectedOption = e.params.data;
                // console.log(selectedOption)
                @this.call('selectProduct', { selectedOption })
                $('#select2-product').val('').trigger('change')
            });
        }
    </script>
@endpush
