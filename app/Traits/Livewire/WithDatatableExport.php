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
    public $data;
    public const TYPE_EXCEL = 'excel';

    public const TYPE_PDF = 'pdf';
    public const TYPE_PDF_DOWNLOAD = 'pdf-download';
    public const TYPE_WORD = 'word';

    private static function export(
        $type,
        $fileName,
        $data,
        $view,
        $request,
        $paperOption = null,
        $columns = null,
        $title = null
    ) {
        if (self::TYPE_EXCEL == $type) {
            return Excel::download(new DatatableExport($request, $data, $view, $type, $columns, $title), "$fileName.xlsx");
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
                    'columns' => $columns,
                    'title' => $title,
                    'type' => $type,
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

   #[On('datatable-export-handler')]
   public function datatableExportHandler($data, $columns, $type, $title, $filters, $fileName)
   {
      return self::export(
         $type,
         $fileName,
         $data,
         "app.layouts.export",
         $filters,
         [
             'size' => 'legal',
             'orientation' => 'portrait',
         ],
         unserialize($columns),
         $title,
     );
   }
}
