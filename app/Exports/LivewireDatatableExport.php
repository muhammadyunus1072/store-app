<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LivewireDatatableExport implements FromView, ShouldAutoSize
{
    const EXPORT_PDF = "pdf";
    const EXPORT_EXCEL = "excel";

    const FOOTER_TYPE_SUM = "sum";

    public function __construct(
        public $view,
        public $title,
        public $subtitles,
        public $columns,
        public $data,
    ) {}

    public function view(): View
    {
        return view(
            $this->view,
            [
                'title' => $this->title,
                'subtitles' => $this->subtitles,
                'columns' => $this->columns,
                'data' => $this->data,
                'type' => self::EXPORT_EXCEL,
            ]
        );
    }
}
