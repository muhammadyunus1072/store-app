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
                <td colspan="{{ 11 + $request['colspan']}}" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 11 + $request['colspan']}}" style="font-weight: bold;">
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
                <td colspan="{{ 3 + $request['colspan']}}" style="font-weight: bold;">
                    Supplier :
                    {{ $request['supplier'] }}
                </td>
            </tr>
            <tr>
                <td colspan="{{ 11 + $request['colspan']}}" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="{{ 11 + $request['colspan']}}" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Tanggal</th>
                <th style="font-weight: bold;">Nomor</th>
                <th style="font-weight: bold;">Supplier</th>
                <th style="font-weight: bold;">Produk</th>
                @if ($request['isInputProductCode'])
                    <th style="font-weight: bold;">Kode</th>
                @endif
                @if ($request['isInputProductExpiredDate'])
                    <th style="font-weight: bold;">Expired Date</th>
                @endif
                @if ($request['isInputProductBatch'])
                    <th style="font-weight: bold;">Batch</th>
                @endif
                <th style="font-weight: bold;">Jumlah</th>
                <th style="font-weight: bold;">Satuan</th>
                <th style="font-weight: bold;">Harga Satuan</th>
                <th style="font-weight: bold;">Jumlah Konversi</th>
                <th style="font-weight: bold;">Satuan Konversi</th>
                <th style="font-weight: bold;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $total_qty = 0;
                $total_value = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $total_qty += $item->converted_quantity;
                    $total_value += $item->value;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>@date($item->transaction_date)</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->supplier_name }}</td>
                    <td>{{ $item->product_name }}</td>
                    @if ($request['isInputProductCode'])
                        <td>{{ $item->code }}</td>
                    @endif
                    @if ($request['isInputProductExpiredDate'])
                        <td>{{ $item->expired_date }}</td>
                    @endif
                    @if ($request['isInputProductBatch'])
                        <td>{{ $item->batch }}</td>
                    @endif
                    <td>{{ $isNumberFormat ? \App\Helpers\General\NumberFormatter::format($item->quantity) : $item->quantity }}</td>
                    <td>{{ $item->unit_detail_name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->price, 0, '.', '.') : $item->price }}</td>
                    <td>{{ $isNumberFormat ? \App\Helpers\General\NumberFormatter::format($item->converted_quantity) : $item->converted_quantity }}</td>
                    <td>{{ $item->main_unit_detail_name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->value, 0, '.', '.') : $item->value }}</td>
                </tr>
            @endforeach
            <thead>
                <tr>
                    <th colspan="{{ 8 + $request['colspan']}}">Total</th>
                    <th>{{ $isNumberFormat ? number_format($total_qty, 0, '.', '.') : $total_qty }}</th>
                    <th></th>
                    <th>{{ $isNumberFormat ? number_format($total_value, 0, '.', '.') : $total_value }}</th>
                </tr>
            </thead>
        </tbody>
    </table>
</body>

</html>
