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
                <td colspan="9" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="9" style="font-weight: bold;">
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
                <td colspan="3" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="9" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Tanggal</th>
                <th style="font-weight: bold;">Nomor</th>
                <th style="font-weight: bold;">Gudang</th>
                <th style="font-weight: bold;">Produk</th>
                <th style="font-weight: bold;">Jumlah</th>
                <th style="font-weight: bold;">Satuan</th>
                <th style="font-weight: bold;">Jumlah Konversi</th>
                <th style="font-weight: bold;">Satuan Konversi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $total = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $total += $item->converted_quantity;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>@dateTime($item->transaction_date)</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->warehouse_name }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $isNumberFormat ? \App\Helpers\General\NumberFormatter::format($item->quantity) : $item->quantity }}</td>
                    <td>{{ $item->unit_detail_name }}</td>
                    <td>{{ $isNumberFormat ? \App\Helpers\General\NumberFormatter::format($item->converted_quantity) : $item->converted_quantity }}</td>
                    <td>{{ $item->main_unit_detail_name }}</td>
                </tr>
            @endforeach
            <thead>
                <tr>
                    <th colspan="7">Total</th>
                    <th>{{ $isNumberFormat ? \App\Helpers\General\NumberFormatter::format($total) : $total }}</th>
                    <th></th>
                </tr>
            </thead>
        </tbody>
    </table>
</body>

</html>
