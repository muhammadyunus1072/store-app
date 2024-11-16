<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DatatableExport implements FromView, ShouldAutoSize
{
    private $view;

    private $collection;
    private $columns;
    private $type;
    private $title;

    private $request;

    public function __construct($request, $collection, $view, $type, $columns = null, $title = null)
    {
        $this->request = $request;
        $this->collection = $collection;
        $this->view = $view;
        $this->type = $type;
        $this->columns = $columns;
        $this->title = $title;
    }

    public function view(): View
    {
        return view($this->view, [
            'request' => $this->request,
            'collection' => $this->collection,
            'type' => $this->type,
            'columns' => $this->columns,
            'title' => $this->title,
            'number_format' => false,
        ]);
    }
}
