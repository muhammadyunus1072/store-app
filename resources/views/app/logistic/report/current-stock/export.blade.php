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
                <td colspan="11" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="11" style="font-weight: bold;">
                    Tanggal :{{ Carbon\Carbon::parse($request['date_start'])->format('Y-m-d') }} s/d
                    {{ Carbon\Carbon::parse($request['date_end'])->format('Y-m-d') }}
                </td>
            </tr>
            <tr>
                <td colspan="3" style="font-weight: bold;">
                    Produk :
                    @foreach ($request['products'] as $index => $product)
                     {{ $index ? ", ".$product : $product }}
                    @endforeach
                </td>
                <td colspan="3" style="font-weight: bold;">
                    Kategori Produk :
                    @foreach ($request['category_products'] as $index => $category_product)
                     {{ $index ? ", ".$category_product : $category_product }}
                    @endforeach
                </td>
                <td colspan="5" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="11" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Nama</th>
                <th style="font-weight: bold;">Satuan</th>
                <th style="font-weight: bold;">Stok Awal</th>
                <th style="font-weight: bold;">Jumlah Pembelian</th>
                <th style="font-weight: bold;">Jumlah Pengeluaran</th>
                <th style="font-weight: bold;">Stok Akhir</th>
                <th style="font-weight: bold;">Nilai Awal</th>
                <th style="font-weight: bold;">Nilai Pembelian</th>
                <th style="font-weight: bold;">Nilai Pengeluaran</th>
                <th style="font-weight: bold;">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $first_stock = 0;
                $purchase_stock = 0;
                $expense_stock = 0;
                $last_stock = 0;

                $first_value = 0;
                $purchase_value = 0;
                $expense_value = 0;
                $last_stock_value = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $first_stock += $item->last_stock - $item->expense_quantity - $item->purchase_quantity;
                    $purchase_stock += $item->purchase_quantity;
                    $expense_stock += $item->expense_quantity;
                    $last_stock += $item->last_stock;

                    $first_value += $item->last_stock_value - $item->expense_value - $item->purchase_value;
                    $purchase_value += $item->purchase_value;
                    $expense_value += $item->expense_value;
                    $last_stock_value += $item->last_stock_value;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->unit_detail_name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock - $item->expense_quantity - $item->purchase_quantity, 0, '.', '.') : $item->last_stock - $item->expense_quantity - $item->purchase_quantity }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->purchase_quantity, 0, '.', '.') : $item->purchase_quantity }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->expense_quantity * -1, 0, '.', '.') : $item->expense_quantity * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock, 0, '.', '.') : $item->last_stock }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock_value - $item->expense_value - $item->purchase_value, 0, '.', '.') : $item->last_stock_value - $item->expense_value - $item->purchase_value }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->purchase_value, 0, '.', '.') : $item->purchase_value }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->expense_value * -1, 0, '.', '.') : $item->expense_value * -1 }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock_value, 0, '.', '.') : $item->last_stock_value }}</td>
                </tr>
            @endforeach
            <thead>
                <tr>
                    <th colspan="3">Total</th>
                    <th>{{ $isNumberFormat ? number_format($first_stock, 0, '.', '.') : $first_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($purchase_stock, 0, '.', '.') : $purchase_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($expense_stock * -1, 0, '.', '.') : $expense_stock * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock, 0, '.', '.') : $last_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($first_value, 0, '.', '.') : $first_value }}</th>
                    <th>{{ $isNumberFormat ? number_format($purchase_value, 0, '.', '.') : $purchase_value }}</th>
                    <th>{{ $isNumberFormat ? number_format($expense_value * -1, 0, '.', '.') : $expense_value * -1 }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock_value, 0, '.', '.') : $last_stock_value }}</th>
                </tr>
            </thead>
        </tbody>
    </table>
</body>

</html>
