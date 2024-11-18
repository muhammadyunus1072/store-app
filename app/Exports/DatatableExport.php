<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DatatableExport implements FromView, ShouldAutoSize
{
    private $view;
    private $columns;
    private $data;
    private $filters;
    private $type;
    private $footerTotal;
    private $fileName;

    public function __construct($view, $columns, $data, $filters, $type, $footerTotal, $fileName)
    {
        $this->view = $view;
        $this->columns = $columns;
        $this->data = $data;
        $this->filters = $filters;
        $this->type = $type;
        $this->footerTotal = $footerTotal;
        $this->fileName = $fileName;
    }

    public function view(): View
    {
        return view($this->view, [
            'columns' => $this->columns,
            'data' => $this->data,
            'filters' => $this->filters,
            'type' => $this->type,
            'footerTotal' => $this->footerTotal,
            'fileName' => $this->fileName,
        ]);
    }
}
