<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CollectionExport implements FromView, ShouldAutoSize
{
    public function __construct(
        public $view,
        public $data,
    ) {}

    public function view(): View
    {
        return view($this->view, [
            'data' => $this->data,
            'isNumberFormat' => false,
        ]);
    }
}
