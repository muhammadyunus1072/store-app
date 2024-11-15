<div>
    @if (empty($data))
        <h4>Produk yang dipilih belum terdapat kartu stok</h4>
    @else
        @php $index=0 @endphp
        <div class='row'>
            <div class='col-md-3 mb-3 fs-4'>
                <label class='fw-bold'>Nama:</label> {{ $productName }}
            </div>
            <div class='col-md-3 mb-3 fs-4'>
                <label class='fw-bold'>Satuan:</label> {{ $productUnitName }}
            </div>
        </div>

        <div class="accordion mt-4" id="accordionExample" wire:ignore.self>
            @foreach ($data as $item)
                <div class="accordion-item" wire:ignore.self>
                    <h2 class="accordion-header" id="heading-{{ $index }}" wire:ignore.self>
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $index }}" aria-expanded="false"
                            aria-controls="collapse-{{ $index }}" wire:ignore.self>
                            Harga: @currency($item['price'])
                            @if ($infoProductCode && $item['code'])
                                / Kode: {{ $item['code'] }}
                            @endif
                            @if ($infoProductExpiredDate && $item['batch'])
                                / Batch: {{ $item['batch'] }}
                            @endif
                            @if ($infoProductBatch && $item['expired_date'])
                                / ED: {{ $item['expired_date'] }}
                            @endif
                        </button>
                    </h2>
                    <div id="collapse-{{ $index }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $index++ }}" data-bs-parent="#accordionExample"
                        wire:ignore.self>
                        <div class="accordion-body" wire:ignore.self>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Stok Awal</th>
                                        <th>Jumlah</th>
                                        <th>Stok Akhir</th>
                                        <th>Nilai Awal</th>
                                        <th>Nilai</th>
                                        <th>Nilai Akhir</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $quantity = $item['start_quantity'];
                                        $value = $item['start_value'];
                                    @endphp
                                    @foreach ($item['histories'] as $history)
                                        <tr>
                                            <td>@date($history->transaction_date)</td>
                                            <td>@currency($quantity)</td>
                                            <td>@currency($history->quantity)</td>
                                            <td>@currency($quantity + $history->quantity)</td>
                                            <td>@currency($value)</td>
                                            <td>@currency($history->quantity * $history->price)</td>
                                            <td>@currency($value + $history->quantity * $history->price)</td>
                                            <td>{!! $history->remarksUrlButton() !!}</td>
                                        </tr>

                                        @php
                                            $quantity += $history->quantity;
                                            $value += $history->quantity * $history->price;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
