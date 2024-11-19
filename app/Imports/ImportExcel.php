<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportExcel implements ToCollection
{
    private $formatCallback;
    private $skip_rows;

    public function __construct(callable $formatCallback = null, $skip_rows = null)
    {
        $this->formatCallback = $formatCallback;
        $this->skip_rows = $skip_rows;
    }

    public function collection(Collection $rows)
    {
        $rows = $this->skip_rows ? $rows->skip($this->skip_rows) : $rows;
        foreach ($rows as $row) {
            $data = call_user_func($this->formatCallback, $row);
        }
    }
}
