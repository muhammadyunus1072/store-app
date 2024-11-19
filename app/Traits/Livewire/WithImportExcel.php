<?php

namespace App\Traits\Livewire;

use App\Imports\ImportExcel;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

trait WithImportExcel
{
    use WithFileUploads;

    public $import_excel = [];

    public function onMount() {}

    public function mount()
    {
        $this->onMount();
    }

    public function storeImport()
    {
        foreach ($this->import_excel as $import_excel) {
            if ($import_excel['data']) {
                $path = $import_excel['data']->store('temp');

                $formatFunction = $this->{$import_excel['format']}();

                $importInstance = new ImportExcel(
                    $formatFunction,
                    isset($import_excel['skip_rows']) ? $import_excel['skip_rows'] : null
                );

                Excel::import($importInstance, Storage::path($path));
                Storage::delete($path);
            }
        }
    }

    public function render()
    {
        return view($this->getView());
    }
}
