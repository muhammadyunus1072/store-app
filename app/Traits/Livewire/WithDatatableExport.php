<?php

namespace App\Traits\Livewire;;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LivewireDatatableExport;
use Maatwebsite\Excel\Facades\Excel;

trait WithDatatableExport
{
    public $showExport = true;

    abstract public function datatableExportFileName(): string;
    abstract public function datatableExportTitle(): string;

    public function datatableExportView()
    {
        return "app.export.livewire-datatable";
    }

    public function datatableExportPaperOption()
    {
        return [
            'size' => 'legal',
            'orientation' => 'portrait',
        ];
    }

    public function datatableExportSubtitle(): array
    {
        return [];
    }

    public function datatableExport($type)
    {
        $columns = array_filter($this->getColumns(), function ($item) {
            return !isset($item['export']) || $item['export'] !== false;
        });

        $data = $this->datatableGetProcessedQuery()->get();
        $title = $this->datatableExportTitle();
        $subtitles = $this->datatableExportSubtitle();
        $fileName = $this->datatableExportFileName();
        $paperOption = $this->datatableExportPaperOption();
        $view = $this->datatableExportView();

        if ($type == LivewireDatatableExport::EXPORT_EXCEL) {
            return Excel::download(
                new LivewireDatatableExport(
                    $view,
                    $title,
                    $subtitles,
                    $columns,
                    $data
                ),
                "$fileName.xlsx"
            );
        } elseif ($type == LivewireDatatableExport::EXPORT_PDF) {
            $pdf = Pdf::loadview(
                $view,
                [
                    'title' => $title,
                    'subtitles' => $subtitles,
                    'columns' => $columns,
                    'data' => $data,
                    'type' => $type,
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
