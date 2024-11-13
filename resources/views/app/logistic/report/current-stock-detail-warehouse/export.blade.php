<!DOCTYPE html>
<html>

<head>
    <title>{{ $request['title'] }}</title>
    <style>
        .table-border {
            border-collapse: collapse;
            font-size: 10px;
        }

        .table-border td {
            border: 1px solid;
            padding: 3px;
        }

        .table-border th {
            border: 1px solid;
            font-weight: bold;
            padding: 3px;
        }
    </style>
</head>

<body>
    <table class="table-border" style="width: 100%">
        <thead>
            <tr>
                <td colspan="{{ 16 + $request['colspan']}}" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 16 + $request['colspan']}}" style="font-weight: bold;">
                    Tanggal :{{ Carbon\Carbon::parse($request['date_start'])->format('Y-m-d') }} s/d
                    {{ Carbon\Carbon::parse($request['date_end'])->format('Y-m-d') }}
                </td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight: bold;">
                    Produk :
                    @foreach ($request['products'] as $index => $product)
                     {{ $index ? ", ".$product : $product }}
                    @endforeach
                </td>
                <td colspan="4" style="font-weight: bold;">
                    Kategori Produk :
                    @foreach ($request['category_products'] as $index => $category_product)
                     {{ $index ? ", ".$category_product : $category_product }}
                    @endforeach
                </td>
                <td colspan="4" style="font-weight: bold;">
                    Gudang :
                    {{ $request['warehouse'] }}
                </td>
                <td colspan="{{ 4 + $request['colspan']}}" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 16 + $request['colspan']}}" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Nama</th> 
                <th style="font-weight: bold;">Harga</th>
                @if ($request['isInputProductCode'])
                    <th style="font-weight: bold;">Kode</th>
                @endif
                @if ($request['isInputProductExpiredDate'])
                    <th style="font-weight: bold;">Expired Date</th>
                @endif
                @if ($request['isInputProductBatch'])
                    <th style="font-weight: bold;">Batch</th>
                @endif
                <th style="font-weight: bold;">Satuan</th>
                <th style="font-weight: bold;">Stok Awal</th>
                <th style="font-weight: bold;">Jumlah Pembelian</th>
                <th style="font-weight: bold;">Jumlah Tranfer Masuk</th>
                <th style="font-weight: bold;">Jumlah Tranfer Keluar</th>
                <th style="font-weight: bold;">Jumlah Pengeluaran</th>
                <th style="font-weight: bold;">Stok Akhir</th>
                <th style="font-weight: bold;">Nilai Awal</th>
                <th style="font-weight: bold;">Nilai Pembelian</th>
                <th style="font-weight: bold;">Nilai Tranfer Masuk</th>
                <th style="font-weight: bold;">Nilai Tranfer Keluar</th>
                <th style="font-weight: bold;">Nilai Pengeluaran</th>
                <th style="font-weight: bold;">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $first_stock = 0;
                $purchase_stock = 0;
                $incoming_tranfer_stock = 0;
                $outgoing_tranfer_stock = 0;
                $expense_stock = 0;
                $last_stock = 0;

                $first_value = 0;
                $purchase_value = 0;
                $incoming_tranfer_value = 0;
                $outgoing_tranfer_value = 0;
                $expense_value = 0;
                $last_stock_value = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $first_stock += $item->last_stock - $item->expense_quantity - $item->purchase_quantity;
                    $purchase_stock += $item->purchase_quantity;
                    $expense_stock += $item->expense_quantity;
                    $incoming_tranfer_stock += $item->incoming_tranfer_quantity;
                    $outgoing_tranfer_stock += $item->outgoing_tranfer_quantity;
                    $last_stock += $item->last_stock;

                    $first_value += $item->last_stock_value - $item->expense_value - $item->purchase_value;
                    $purchase_value += $item->purchase_value;
                    $incoming_tranfer_value += $item->incoming_tranfer_value;
                    $outgoing_tranfer_value += $item->outgoing_tranfer_value;
                    $expense_value += $item->expense_value;
                    $last_stock_value += $item->last_stock_value;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->price, 0, '.', '.') : $item->price }}</td>
                    @if ($request['isInputProductCode'])
                        <td>{{ $item->code }}</td>
                    @endif
                    @if ($request['isInputProductExpiredDate'])
                        <td>{{ $item->expired_date }}</td>
                    @endif
                    @if ($request['isInputProductBatch'])
                        <td>{{ $item->batch }}</td>
                    @endif
                    <td>{{ $item->unit_detail_name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock - $item->expense_quantity - $item->purchase_quantity, 0, '.', '.') : $item->last_stock - $item->expense_quantity - $item->purchase_quantity }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->purchase_quantity, 0, '.', '.') : $item->purchase_quantity }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->incoming_tranfer_quantity, 0, '.', '.') : $item->incoming_tranfer_quantity }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->outgoing_tranfer_quantity * -1, 0, '.', '.') : $item->outgoing_tranfer_quantity * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->expense_quantity * -1, 0, '.', '.') : $item->expense_quantity * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock, 0, '.', '.') : $item->last_stock }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock_value - $item->expense_value - $item->purchase_value, 0, '.', '.') : $item->last_stock_value - $item->expense_value - $item->purchase_value }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->purchase_value, 0, '.', '.') : $item->purchase_value }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->incoming_tranfer_value, 0, '.', '.') : $item->incoming_tranfer_value }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->outgoing_tranfer_value * -1, 0, '.', '.') : $item->outgoing_tranfer_value * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->expense_value * -1, 0, '.', '.') : $item->expense_value * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock_value, 0, '.', '.') : $item->last_stock_value }}</td>
                </tr>
            @endforeach
            <tfoot>
                <tr>
                    <th colspan="{{ 4 + $request['colspan'] }}">Total</th>
                    <th>{{ $isNumberFormat ? number_format($first_stock, 0, '.', '.') : $first_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($purchase_stock, 0, '.', '.') : $purchase_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($incoming_tranfer_stock, 0, '.', '.') : $incoming_tranfer_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($outgoing_tranfer_stock * -1, 0, '.', '.') : $outgoing_tranfer_stock * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($expense_stock * -1, 0, '.', '.') : $expense_stock * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock, 0, '.', '.') : $last_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($first_value, 0, '.', '.') : $first_value }}</th>
                    <th>{{ $isNumberFormat ? number_format($purchase_value, 0, '.', '.') : $purchase_value }}</th>
                    <th>{{ $isNumberFormat ? number_format($incoming_tranfer_value, 0, '.', '.') : $incoming_tranfer_value }}</th>
                    <th>{{ $isNumberFormat ? number_format($outgoing_tranfer_value * -1, 0, '.', '.') : $outgoing_tranfer_value * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($expense_value * -1, 0, '.', '.') : $expense_value * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock_value, 0, '.', '.') : $last_stock_value }}</th>
                </tr>
            </tfoot>
        </tbody>
    </table>
</body>

</html>
