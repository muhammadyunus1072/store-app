<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ImportExcel implements ToCollection, WithCalculatedFormulas
{
    public function __construct(
        public $obj,
        public $onImportCallback,
        public $skip = null
    ) {}

    public function collection(Collection $rows)
    {
        $rows = $this->skip ? $rows->skip($this->skip) : $rows;

        foreach ($rows as $row) {
            call_user_func([$this->obj, $this->onImportCallback], $row);
        }
    }
}
