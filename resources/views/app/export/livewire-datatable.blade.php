<!DOCTYPE html>
<html>

<head>
    <title>Export</title>
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
                    {{ $title }}
                </td>
            </tr>

            {{-- FILTER --}}
            @foreach ($subtitles as $key => $value)
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
            {{-- CONTENT --}}
            @foreach ($data as $index => $item)
                <tr>
                    @foreach ($columns as $i => $col)
                        @php
                            if (
                                isset($columns[$i]['export_footer_type']) &&
                                isset($columns[$i]['export_footer_data'])
                            ) {
                                if (
                                    $columns[$i]['export_footer_type'] ==
                                    App\Exports\LivewireDatatableExport::FOOTER_TYPE_SUM
                                ) {
                                    if (!isset($columns[$i]['export_footer_value'])) {
                                        $columns[$i]['export_footer_value'] = 0;
                                    }

                                    $columns[$i]['export_footer_value'] += call_user_func(
                                        $col['export_footer_data'],
                                        $item,
                                    );
                                }
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

                        @if (isset($col['export']) && is_callable($col['export']))
                            <td {!! $cell_class !!} {!! $cell_style !!}>
                                {!! call_user_func($col['export'], $item, $index, $type) !!}
                            </td>
                        @elseif (isset($col['render']) && is_callable($col['render']))
                            <td {!! $cell_class !!} {!! $cell_style !!}>
                                {!! call_user_func($col['render'], $item, $index, $type) !!}
                            </td>
                        @elseif (isset($col['key']))
                            <td {!! $cell_class !!} {!! $cell_style !!}>
                                {{ $item->{$col['key']} }}
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach

            {{-- FOOTER --}}
            <tr>
                @foreach ($columns as $i => $col)
                    @if (isset($columns[$i]['export_footer_type']) && isset($columns[$i]['export_footer_data']))
                        @if (isset($col['export_footer_format']) && is_callable($col['export_footer_format']))
                            <td>
                                {!! call_user_func($col['export_footer_format'], $col['export_footer_value'], $type) !!}
                            </td>
                        @else
                            <td>
                                {{ $col['export_footer_value'] }}
                            </td>
                        @endif
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>
        </tbody>

    </table>
</body>

</html>
