<div>
    @if (empty($productName))
        <h4>Belum Terdapat Produk Yang Dipilih</h4>
    @else
        <div class='row'>
            <div class='col-md-3 mb-3 fs-4'>
                <label class='fw-bold'>Nama:</label> {{ $productName }}
            </div>
            <div class='col-md-3 mb-3 fs-4'>
                <label class='fw-bold'>Satuan:</label> {{ $productUnitName }}
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-auto">
                <label>Export Data:</label>
            </div>
            <div class="col-auto">
                <button class="btn btn-light-success btn-sm"
                    wire:click="export('{{ App\Helpers\General\ExportHelper::TYPE_EXCEL }}')">
                    <i class="fa fa-file-excel"></i>
                    Export Excel
                </button>
            </div>
            <div class="col-auto">
                <button class="btn btn-light-danger btn-sm"
                    wire:click="export('{{ App\Helpers\General\ExportHelper::TYPE_PDF }}')">
                    <i class="fa fa-file-pdf"></i>
                    Export PDF
                </button>
            </div>
        </div>

        <hr>

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
                    $quantity = $startQuantity;
                    $value = $startValue;
                @endphp
                @foreach ($histories as $history)
                    <tr>
                        <td>@date($history['transaction_date'])</td>
                        <td>@currency($quantity)</td>
                        <td>@currency($history['quantity'])</td>
                        <td>@currency($quantity + $history['quantity'])</td>
                        <td>@currency($value)</td>
                        <td>@currency($history['quantity'] * $history['price'])</td>
                        <td>@currency($value + $history['quantity'] * $history['price'])</td>
                        <td>{!! $history['remarksUrlButton'] !!}</td>
                    </tr>

                    @php
                        $quantity += $history['quantity'];
                        $value += $history['quantity'] * $history['price'];
                    @endphp
                @endforeach
            </tbody>
        </table>
    @endif
</div>
