<?php

namespace App\Traits\Livewire;

use Exception;
use App\Imports\ImportExcel;
use Livewire\WithFileUploads;
use App\Helpers\General\Alert;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

trait WithImportExcel
{
    use WithFileUploads;

    public $import_excel = [];

    public function storeImport($index)
    {
        try {
            DB::beginTransaction();

            if (!isset($this->import_excel[$index])) {
                Alert::fail($this, "Gagal", "Gagal Menyimpan Data");
                return;
            }
            if (!$this->import_excel[$index]['data']) {
                Alert::fail($this, "Gagal", "File Belum Dipilih");
                return;
            }

            $import_excel = $this->import_excel[$index];
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

            DB::commit();

            Alert::success(
                $this,
                "Berhasil",
                "Data Berhasil Disimpan",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }
}
