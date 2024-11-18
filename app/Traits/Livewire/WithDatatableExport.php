<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;
use App\Helpers\General\ExportHelper;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DatatableExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;

trait WithDatatableExport
{
    public const TYPE_EXCEL = 'excel';
    public const TYPE_PDF = 'pdf';
    
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



    #[On('datatable-export')]
    public function datatableExport(
        $type
    ) {
        $columns = $this->getColumns();
        $data = $this->datatableGetProcessedQuery()->get();
        $filters = $this->datatableExportFilter();
        $fileName = $this->datatableExportFileName();
        $paperOption = $this->datatableExportPaperOption();
        $footerTotal = $this->calculateColspans();
        
        $view = "app.layouts.export";
        if (self::TYPE_EXCEL == $type) {
            return Excel::download(new DatatableExport($view, $columns, $data, $filters, $type, $footerTotal, $fileName), "$fileName.xlsx");
        } 
        elseif (self::TYPE_PDF == $type) {
            $pdf = Pdf::loadview(
                $view,
                [
                    'columns' => $columns,
                    'data' => $data,
                    'filters' => $filters,
                    'type' => $type,
                    'footerTotal' => $footerTotal,
                    'fileName' => $fileName,
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
                    'Content-Disposition' => 'inline; filename="'.$fileName.'.pdf"',
                ]
            );
        }
    }
}
