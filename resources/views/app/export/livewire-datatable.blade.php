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
                <td colspan="{{ count($columns) }}" style="text-align: center; font-weight: bold;">
                    {{ $fileName }}
                </td>
            </tr>

            {{-- FILTER --}}
            @foreach ($filters as $key => $value)
                @if ($value)
                    <tr>
                        <td colspan="{{ count($columns) }}" style="font-weight: bold;">
                            {{ $key }} :{{ $value }}
                        </td>
                    </tr>
                @endif
            @endforeach

            {{-- HEADER COLUMN --}}
            <tr>
                <td colspan="{{ count($columns) }}" style="border: 0px; padding:8px">
            </tr>
            <tr>
                @foreach ($columns as $index => $col)
                    <th>
                        <div class="fs-6 p-2">
                            {{ $col['name'] }}
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @php
                $isNumberFormat = $type == App\Helpers\General\ExportHelper::TYPE_PDF;
            @endphp

            @foreach ($data as $index => $item)
                <tr>
                    @foreach ($columns as $i => $col)
                        @php
                            if (!empty($footerTotal) && isset($footerTotal[$i])) 
                            {    
                                (isset($col['export_footer_total']) && $col['export_footer_total'] == true)
                                    ? ($footerTotal[$i]['value'] += (float) str_replace(
                                        ['.', ','],
                                        ['', '.'],
                                        isset($col['render']) ? $col['render']($item) : $col['key'],
                                    ))
                                    : null;
                            }

                            $cell_colspan = '';
                            if (isset($col['colspan'])) {
                                $cell_colspan = is_callable($col['colspan'])
                                    ? call_user_func($col['colspan'], $item, $index)
                                    : $col['colspan'];
                                $cell_colspan = "colspan='{$cell_colspan}'";
                            }

                            $cell_rowspan = '';
                            if (isset($col['rowspan'])) {
                                $cell_rowspan = is_callable($col['rowspan'])
                                    ? call_user_func($col['rowspan'], $item, $index)
                                    : $col['rowspan'];
                                $cell_rowspan = "rowspan='{$cell_rowspan}'";
                            }

                            $cell_style = '';
                            if (isset($col['style'])) {
                                $cell_style = is_callable($col['style'])
                                    ? call_user_func($col['style'], $item, $index)
                                    : $col['style'];
                                $cell_style = "style='{$cell_tyle}'";
                            }

                            $cell_class = '';
                            if (isset($col['class'])) {
                                $cell_class = is_callable($col['class'])
                                    ? call_user_func($col['class'], $item, $index)
                                    : $col['class'];
                                $cell_class = "class='{$cell_class}'";
                            }
                        @endphp

                        @if(isset($col['export']) && is_callable($col['export']))
                            <td {!! $cell_colspan !!} {!! $cell_rowspan !!} {!! $cell_class !!}
                                {!! $cell_style !!}>
                                {!! call_user_func($col['export'], $item, $index, $type) !!}
                            </td>
                        @elseif (isset($col['render']) && is_callable($col['render']))
                            <td {!! $cell_colspan !!} {!! $cell_rowspan !!} {!! $cell_class !!}
                                {!! $cell_style !!}>
                                {!! call_user_func($col['render'], $item, $index, $type) !!}
                            </td>
                        @elseif (isset($col['key']))
                            <td {!! $cell_colspan !!} {!! $cell_rowspan !!} {!! $cell_class !!}
                                {!! $cell_style !!}>
                                {{ $item->{$col['key']} }}
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <td colspan="{{ count($columns) }}" style="border: 0px; padding:8px">
            </tr>
        </tbody>
        @if (!empty($footerTotal))
            <tbody>
                <tr>
                    @foreach ($footerTotal as $index => $col)
                        <th colspan="{{ $col['colspan'] }}">
                            
                            {{ isset($columns[$index]['export_footer_total']) 
                                ? (is_callable($columns[$index]['export_footer_total']) 
                                        ? $columns[$index]['export_footer_total']($col['value'], $index, $type) 
                                        : ($columns[$index]['export_footer_total'] === true 
                                            ? ($isNumberFormat ? number_format($col['value'], 0, '.', '.') : $col['value']) 
                                            : $columns[$index]['export_footer_total'])
                                    )
                                : ($isNumberFormat ? number_format($col['value'], 0, '.', '.') : $col['value']) }}
                        </th>
                    @endforeach
                </tr>
            </tbody>
        @endif
    </table>
</body>

</html>
