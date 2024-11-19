<?php

namespace App\Helpers\General;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CollectionExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportHelper
{
    public const TYPE_EXCEL = 'excel';
    public const TYPE_PDF = 'pdf';

    public static function export(
        $type,
        $fileName,
        $view,
        $data,
        $paperOption = null,
    ) {
        if (self::TYPE_EXCEL == $type) {
            return Excel::download(new CollectionExport($view, $data), "$fileName.xlsx");
        } else {
            $pdf = Pdf::loadview(
                $view,
                [
                    'data' => $data,
                    'isNumberFormat' => true,
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
