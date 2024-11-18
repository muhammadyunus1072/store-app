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
                <td colspan="{{ 12 + $request['colspan']}}" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 12 + $request['colspan']}}" style="font-weight: bold;">
                    Tanggal :{{ Carbon\Carbon::parse($request['dateStart'])->format('Y-m-d') }} s/d
                    {{ Carbon\Carbon::parse($request['dateEnd'])->format('Y-m-d') }}
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
                    @foreach ($request['categoryProductIds'] as $index => $category_product)
                     {{ $index ? ", ".$category_product : $category_product }}
                    @endforeach
                </td>
                <td colspan="{{ 4 + $request['colspan']}}" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 12 + $request['colspan']}}" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Tanggal</th>
                <th style="font-weight: bold;">Nama Produk</th>
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
                <th style="font-weight: bold;">Jumlah</th>
                <th style="font-weight: bold;">Stok Akhir</th>
                <th style="font-weight: bold;">Nilai Awal</th>
                <th style="font-weight: bold;">Nilai</th>
                <th style="font-weight: bold;">Nilai Akhir</th>
                <th style="font-weight: bold;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $first_stock = 0;
                $quantity = 0;
                $last_stock = 0;

                $first_value = 0;
                $value = 0;
                $last_stock_value = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $first_stock += $item->start_stock;
                    $quantity += $item->quantity;
                    $last_stock += $item->last_stock;

                    $first_value += $item->start_stock * $item->price;
                    $value += $item->quantity * $item->price;
                    $last_stock_value += $item->last_stock * $item->price;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->transaction_date }}</td>
                    <td>{{ $item->product_name }}</td>
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
                    <td>{{ $isNumberFormat ? number_format($item->start_stock, 0, '.', '.') : $item->start_stock,}}</td>
                    <td>{{ $isNumberFormat ? number_format(abs($item->quantity), 0, '.', '.') : abs($item->quantity),}}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock, 0, '.', '.') : $item->last_stock,}}</td>
                    <td>{{ $isNumberFormat ? number_format($item->start_stock * $item->price, 0, '.', '.') : $item->start_stock * $item->price,}}</td>
                    <td>{{ $isNumberFormat ? number_format(abs($item->quantity * $item->price), 0, '.', '.') : abs($item->quantity * $item->price),}}</td>
                    <td>{{ $isNumberFormat ? number_format($item->last_stock * $item->price, 0, '.', '.') : $item->last_stock * $item->price,}}</td>
                    <td>{{ $item->remarksTable->remarksTableInfo()['translated_name']." ".$item->remarksMasterTable->number }}</td>
                </tr>
            @endforeach
            <thead>
                <tr>
                    <th colspan="{{ 5 + $request['colspan']}}">Total</th>
                    <th>{{ $isNumberFormat ? number_format($first_stock, 0, '.', '.') : $first_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format(abs($quantity), 0, '.', '.') : abs($quantity) }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock, 0, '.', '.') : $last_stock }}</th>
                    <th>{{ $isNumberFormat ? number_format($first_value, 0, '.', '.') : $first_value }}</th>
                    <th>{{ $isNumberFormat ? number_format(abs($value), 0, '.', '.') : abs($value) }}</th>
                    <th>{{ $isNumberFormat ? number_format($last_stock_value, 0, '.', '.') : $last_stock_value }}</th>
                    <th></th>
                </tr>
            </thead>
        </tbody>
    </table>
</body>

</html>
