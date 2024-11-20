<?php

namespace App\Traits\Livewire;;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LivewireDatatableExport;
use Maatwebsite\Excel\Facades\Excel;

trait WithDatatableExport
{
    public $showExport = true;

    abstract public function datatableExportFileName(): string;

    public function datatableExportPaperOption()
    {
        return [
            'size' => 'legal',
            'orientation' => 'portrait',
        ];
    }

    public function datatableExportCustomColumn()
    {
        return [];
    }

    public function datatableExportExcludeColumn()
    {
        return [];
    }

    public function datatableExportEnableFooterTotal()
    {
        return [];
    }

    public function datatableExportFilter(): array
    {
        return [];
    }

    function calculateColspans($columns)
    {
        $footerIndexes = $this->datatableExportEnableFooterTotal();

        $colspans = [];
        $totalColumns = count($columns);

        foreach ($footerIndexes as $key => $footerIndex) {
            $nextIndex = isset($footerIndexes[$key + 1]) ? $footerIndexes[$key + 1] : $totalColumns;
            $colspan = $nextIndex - $footerIndex + ((!$key) ? $footerIndex : 0);
            $colspans[$footerIndex] = [
                'colspan' => $colspan,
                'value' => 0
            ];
        }

        return $colspans;
    }

    public function datatableExport($type)
    {
        $columns = collect($this->getColumns());
        $excludeColumn = $this->datatableExportExcludeColumn();
        foreach ($excludeColumn as $index) {
            $columns->forget($index);
        }
        $customColumns = $this->datatableExportCustomColumn();
        foreach($customColumns as $customKey => $function)
        {
            $columns = $columns->transform(function ($item, $key) use ($function, $customKey) {
                if ($key === $customKey) {
                    $item['render'] = $function;
                }
                return $item;
            });
        }
        
        $data = $this->datatableGetProcessedQuery()->get();
        $filters = $this->datatableExportFilter();
        $fileName = $this->datatableExportFileName();
        $paperOption = $this->datatableExportPaperOption();
        $footerTotal = $this->calculateColspans($columns);

        $view = "app.export.livewire-datatable";
        if ('excel' == $type) {
            return Excel::download(new LivewireDatatableExport($view, $columns, $data, $filters, $type, $footerTotal, $fileName), "$fileName.xlsx");
        } elseif ('pdf' == $type) {
            $pdf = Pdf::loadview(
                $view,
                [
                    'columns' => $columns,
                    'data' => $data,
                    'filters' => $filters,
                    'type' => $type,
                    'footerTotal' => $footerTotal,
                    'fileName' => $fileName,
                    'numberFormat' => true,
                ],
            );

            if ($paperOption) {
                $pdf = $pdf->setPaper($paperOption['size'], $paperOption['orientation']);
            }

            return response()->stream(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '.pdf"',
                ]
            );
        }
    }
}
