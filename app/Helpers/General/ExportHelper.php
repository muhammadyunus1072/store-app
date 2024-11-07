<?php

namespace App\Helpers\General;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CollectionExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportHelper
{
    public const TYPE_EXCEL = 'excel';

    public const TYPE_PDF = 'pdf';
    public const TYPE_PDF_DOWNLOAD = 'pdf-download';
    public const TYPE_WORD = 'word';

    public static function export(
        $type,
        $fileName,
        $data,
        $view,
        $request,
        $paperOption = null,
    ) {
        if (self::TYPE_EXCEL == $type) {
            return Excel::download(new CollectionExport($request, $data, $view), "$fileName.xlsx");
        } 
        elseif (self::TYPE_WORD == $type) {
            $publicPath = $view;
            $template = new TemplateProcessor($publicPath);

            $template->setValues($data['normal_replacement']); 
            if(isset($data['block_replacement']))
            {
                foreach($data['block_replacement'] as $block_name => $block_replacement)
                {
                    $template->cloneBlock($block_name, 0, true, false, $block_replacement);

                }
            }          

            if(isset($data['table_replacement']))
            {
                foreach ($data['table_replacement'] as $key => $value) {
                    $template->cloneRowAndSetValues($key, $value);
                }
            }

            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0777, true);
            }

            $filePath = $tempPath . '/' . $fileName .'.docx';
    
            $template->saveAs($filePath);
            return response()->download($filePath)->deleteFileAfterSend(false);
        } 
        else {
            $pdf = Pdf::loadview(
                $view,
                [
                    'request' => $request,
                    'collection' => $data,
                    'number_format' => true,
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
