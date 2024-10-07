<form wire:submit="store">
    {{-- {{dd($purchaseRequestProducts)}} --}}
    <div class='row'>
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

            @foreach ($purchaseRequestProducts as $index => $item)
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
                        <p class="form-control">{{$purchaseRequestProducts[$index]['product_text']}}</p>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Satuan</label>
                        @endif
                        
                        <select class="form-select @error('type') is-invalid @enderror" wire:model.blur="purchaseRequestProducts.{{ $index }}.unit_detail_id">
                            @foreach ($purchaseRequestProducts[$index]['unit_detail_choice'] as $item)
                                <option value="{{ $item['enc_id'] }}">{{ $item['name'] }} ({{$item['value']}})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        @if (!$index)
                            <label class='fw-bold'>Jumlah Diminta</label>
                        @endif
                        <input type="text" class="form-control currency"
                            wire:model="purchaseRequestProducts.{{ $index }}.quantity" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <h3 class="fw-bold">Data Persetujuan</h3>
    <hr>
    <div class='row'>
        <div class="col-md-12 mb-4">
            <label>Catatan</label>
            <textarea cols="30" rows="4" class="form-control @error('note') is-invalid @enderror" wire:model="approval_note" ></textarea>

            @error('note')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="col-md-4 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    wire:model.live="is_sequentially">
                <label class="form-check-label ms-2 mt-1">
                    Harus Berurutan
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- INVOICE DETAIL --}}
        <div class="col-md-12 mb-3">
            <label>Data Pengguna</label>
            
            <div class="col-md-12 mb-4">
                <div class="w-100" wire:ignore>
                    <select id="select2-user" class="form-select">
                    </select>
                </div>
            </div>

            @foreach ($approvalUsers as $index => $item)
                <div class="row my-2" wire:key="{{ $item['key'] }}">
                    <div class="col-auto d-flex mb-3">
                        <button type="button" class="btn btn-danger mx-auto align-self-end h-auto"
                            wire:click="removeApprover({{ $index }})">
                            <i class='fa fa-times'></i>
                        </button>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Nama</label>
                        @endif
                        <p class="form-control">{{$approvalUsers[$index]['user_text']}}</p>
                    </div>
                    <div class="col-md-4">
                        @if (!$index)
                            <label class='fw-bold'>Posisi</label>
                        @endif
                        <input type="text" class="form-control currency"
                            wire:model="approvalUsers.{{ $index }}.position" />
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
            // Select2 User
            $('#select2-user').select2({
                placeholder: "Pilih Pengguna Menyetujui",
                ajax: {
                    url: "{{ route('purchase_request.get.user') }}",
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
            
            $('#select2-user').on('select2:select', function (e) {
                // Triggered when an option is selected
                var selectedOption = e.params.data;
                // console.log(selectedOption)
                @this.call('selectUser', { selectedOption })
                $('#select2-user').val('').trigger('change')
            });

            // Select2 CategoryProduct
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
