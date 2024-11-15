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
                <td colspan="5" style="text-align: center; font-weight: bold;">
                    {{ $request['title'] }}
                </td>
            </tr>

            <tr>
                <td colspan="5" style="font-weight: bold;">
                    Tanggal :{{ Carbon\Carbon::parse($request['date_start'])->format('Y-m-d') }} s/d
                    {{ Carbon\Carbon::parse($request['date_end'])->format('Y-m-d') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">
                    Supplier :
                    {{ $request['supplier'] }}
                </td>
                <td colspan="3" style="font-weight: bold;">
                    Kata Kunci :{{ $request['keyword'] }}
                </td>
            </tr>

            <tr>
                <td colspan="5" style="border: 0px; padding:8px">
            </tr>

            <tr>
                <th style="font-weight: bold;">#</th>
                <th style="font-weight: bold;">Tanggal</th>
                <th style="font-weight: bold;">Nomor</th>
                <th style="font-weight: bold;">Supplier</th>
                <th style="font-weight: bold;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @php
                $isNumberFormat = $request['type'] == App\Helpers\General\ExportHelper::TYPE_PDF;
                $total_value = 0;
            @endphp

            @foreach ($collection as $index => $item)
                @php
                    $total_value += $item->value;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>@date($item->transaction_date)</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->supplier_name }}</td>
                    <td>{{ $isNumberFormat ? number_format($item->value, 0, '.', '.') : $item->value }}</td>
                </tr>
            @endforeach
            <thead>
                <tr>
                    <th colspan="4">Total</th>
                    <th>{{ $isNumberFormat ? number_format($total_value, 0, '.', '.') : $total_value }}</th>
                </tr>
            </thead>
        </tbody>
    </table>
</body>

</html>
