<form wire:submit="store">
    {{-- {{dd($purchaseOrderProducts)}} --}}
    <div class='row'>
        <div class="col-md-4 mb-3">
            <label>Peminta Gudang</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-warehouse_requester" class="form-select">
                        @if ($objId)
                            <option value="{{$warehouse_requester_id}}">{{$warehouse_requester_text}}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <label>Permintaan Gudang</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-warehouse_requested" class="form-select">
                        @if ($objId)
                            <option value="{{$warehouse_requested_id}}">{{$warehouse_requested_text}}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <label>Tanggal Permintaan</label>
            <input type="date" class="form-control @error('request_date') is-invalid @enderror" wire:model="request_date" />

            @error('request_date')
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
            <label>Data Permintaan</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-product" class="form-select">
                    </select>
                </div>
            </div>

            @foreach ($stockRequestProducts as $index => $item)
                
                <div class="my-2 row">
                    <div class="col-md-4">
                        
                            <label class='fw-bold'>Produk</label>
                        
                        <p class="form-control">{{$stockRequestProducts[$index]['product_text']}}</p>
                    </div>
                    <div class="col-md-2">
                        
                            <label class='fw-bold'>Satuan</label>
                        
                        
                        <select class="form-select @error('type') is-invalid @enderror" wire:model.blur="stockRequestProducts.{{ $index }}.unit_detail_id">
                            @foreach ($stockRequestProducts[$index]['unit_detail_choice'] as $unit)
                                <option value="{{ $unit['id'] }}">{{ $unit['name'] }} ({{$unit['value']}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        
                            <label class='fw-bold'>Jumlah</label>
                        
                        <input type="text" class="form-control currency"
                            wire:model.blur="stockRequestProducts.{{ $index }}.quantity" />
                    </div>

                    <div class="col-auto d-flex">
                        <button type="button" class="btn btn-danger mx-auto align-self-end h-auto mb-4"
                            wire:click="removeDetail({{ $index }})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <hr>
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

            // Select2 Warehouse Requester
            $('#select2-warehouse_requester').select2({
                placeholder: "Pilih Peminta Gudang",
                ajax: {
                    url: "{{ route('stock_request.get.warehouse') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            access: 1,
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

            $('#select2-warehouse_requester').on('select2:select', function (e) {
                // Triggered when an option is selected
                var selectedOption = e.params.data;
                @this.call('setWarehouseRequester',  { selectedOption } )
            });

            // Select2 Warehouse Requested
            $('#select2-warehouse_requested').select2({
                placeholder: "Pilih Permintaan Gudang",
                ajax: {
                    url: "{{ route('stock_request.get.warehouse') }}",
                    dataType: "json",
                    type: "GET",
                    data: function(params) {
                        return {
                            access: 1,
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

            $('#select2-warehouse_requested').on('select2:select', function (e) {
                // Triggered when an option is selected
                var selectedOption = e.params.data;
                @this.call('setWarehouseRequested',  { selectedOption } )
            });

            // Select2 Product
            $('#select2-product').select2({
                placeholder: "Pilih Produk",
                ajax: {
                    url: "{{ route('stock_request.get.product') }}",
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
