<!DOCTYPE html>
<html>

<head>
    <title>Data</title>
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
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Kartu Stok
                </td>
            </tr>

            @foreach ($data['filters'] as $title => $value)
                <tr>
                    <td colspan="8" style="font-weight: bold;">
                        {{ $title }} : {{ $value }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <th style="font-weight: bold;">Tanggal</th>
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
                $quantity = $data['startQuantity'];
                $value = $data['startValue'];
            @endphp
            @foreach ($data['histories'] as $history)
                <tr>
                    <td>@date($history['transaction_date'])</td>
                    @if ($isNumberFormat)
                        <td style="text-align: right">@currency($quantity)</td>
                        <td style="text-align: right">@currency($history['quantity'])</td>
                        <td style="text-align: right">@currency($quantity + $history['quantity'])</td>
                        <td style="text-align: right">@currency($value)</td>
                        <td style="text-align: right">@currency($history['quantity'] * $history['price'])</td>
                        <td style="text-align: right">@currency($value + $history['quantity'] * $history['price'])</td>
                    @else
                        <td style="text-align: right">{{ $quantity }}</td>
                        <td style="text-align: right">{{ $history['quantity'] }}</td>
                        <td style="text-align: right">{{ $quantity + $history['quantity'] }}</td>
                        <td style="text-align: right">{{ $value }}</td>
                        <td style="text-align: right">{{ $history['quantity'] * $history['price'] }}</td>
                        <td style="text-align: right">{{ $value + $history['quantity'] * $history['price'] }}</td>
                    @endif
                    <td>{!! $history['remarksUrlButton'] !!}</td>
                </tr>
                @php
                    $quantity += $history['quantity'];
                    $value += $history['quantity'] * $history['price'];
                @endphp
            @endforeach
        </tbody>
    </table>
</body>

</html>
