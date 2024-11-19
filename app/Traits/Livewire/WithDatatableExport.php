<?php

namespace App\Traits\Livewire;;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LivewireDatatableExport;
use Maatwebsite\Excel\Facades\Excel;

trait WithDatatableExport
{
    const TYPE_EXCEL = 'excel';
    const TYPE_PDF = 'pdf';

    public $showExport = true;

    abstract public function datatableExportFileName(): string;
    abstract public function datatableExportFilter(): array;

    public function datatableExportPaperOption()
    {
        return [
            'size' => 'legal',
            'orientation' => 'portrait',
        ];
    }

    public function datatableExportEnableFooterTotal()
    {
        return [];
    }

    function calculateColspans()
    {
        $columns = $this->getColumns();
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
        $columns = $this->getColumns();
        $data = $this->datatableGetProcessedQuery()->get();
        $filters = $this->datatableExportFilter();
        $fileName = $this->datatableExportFileName();
        $paperOption = $this->datatableExportPaperOption();
        $footerTotal = $this->calculateColspans();

        $view = "app.export.livewire-datatable";
        if (self::TYPE_EXCEL == $type) {
            return Excel::download(new LivewireDatatableExport($view, $columns, $data, $filters, $type, $footerTotal, $fileName), "$fileName.xlsx");
        } elseif (self::TYPE_PDF == $type) {
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
