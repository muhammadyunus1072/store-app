<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
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
                <td colspan="{{count($columns)}}" style="text-align: center; font-weight: bold;">
                    {{ $title }}
                </td>
            </tr>
            @foreach ($request as $key => $value)
                <tr>
                    <td colspan="{{count($columns)}}" style="font-weight: bold;">
                        {{$key}} :{{ $value }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td colspan="{{count($columns)}}" style="border: 0px; padding:8px">
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

                $withFooter = false;
                $footerData = [];
                foreach ($columns as $col) {
                    if (isset($col['withFooter']) && $col['withFooter']) {
                        $withFooter = true;
                        $footerData[$col['name']] = 0;
                    }
                }
            @endphp


            @foreach ($collection as $index => $item)
                <tr>
                    @foreach ($columns as $col)
                        @php
                            if($withFooter && (isset($col['withFooter']) && $col['withFooter']) && !isset($col['footer']))
                            {
                                $footerData[$col['name']] += $col['render']($item);
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

                        <td {!! $cell_colspan !!} {!! $cell_rowspan !!} {!! $cell_class !!} {!! $cell_style !!}>
                            {{ (is_numeric($col['render']( $item, $index)) && $isNumberFormat) ? number_format($col['render']( $item, $index), 0, '.', '.') : $col['render']( $item, $index) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td colspan="{{count($columns)}}" style="border: 0px; padding:8px">
            </tr>
        </tbody>
        @if($withFooter)

        <thead>
            <tr>
                @foreach ($columns as $col)
                    @php
                        $cell_colspan = '';
                        if (isset($col['footerColspan'])) {
                            $cell_colspan = is_callable($col['footerColspan'])
                                ? call_user_func($col['footerColspan'], $item, $index)
                                : $col['footerColspan'];
                            $cell_colspan = "colspan='{$cell_colspan}'";
                        }

                        $cell_rowspan = '';
                        if (isset($col['footerRowspan'])) {
                            $cell_rowspan = is_callable($col['footerRowspan'])
                                ? call_user_func($col['footerRowspan'], $item, $index)
                                : $col['footerRowspan'];
                            $cell_rowspan = "rowspan='{$cell_rowspan}'";
                        }

                        $cell_style = '';
                        if (isset($col['footerStyle'])) {
                            $cell_style = is_callable($col['footerStyle'])
                                ? call_user_func($col['footerStyle'], $item, $index)
                                : $col['footerStyle'];
                            $cell_style = "style='{$cell_style}'";
                        }

                        $cell_class = '';
                        if (isset($col['footerClass'])) {
                            $cell_class = is_callable($col['footerClass'])
                                ? call_user_func($col['footerClass'], $item, $index)
                                : $col['footerClass'];
                            $cell_class = "class='{$cell_class}'";
                        }
                    @endphp
                    @if (isset($col['withFooter']) && $col['withFooter'])
                        <th {!! $cell_colspan !!} {!! $cell_rowspan !!} {!! $cell_class !!} {!! $cell_style !!}>
                            {{ isset($col['footer']) ? $col['footer'] : ($isNumberFormat ? number_format($footerData[$col['name']], 0, '.', '.') : $footerData[$col['name']]) }}
                        </th>
                    @endif
                @endforeach
            </tr>
        </thead>
        @endif
    </table>
</body>

</html>
