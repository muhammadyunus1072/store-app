<?php

namespace App\Traits\Livewire;

use Exception;
use Livewire\WithFileUploads;
use App\Imports\ImportExcel;
use App\Helpers\General\Alert;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
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

            $importItem = $this->import_excel[$index];

            // CALLBACK: onImportStart
            if (isset($importItem['onImportStart'])) {
                if (is_callable([$this, $importItem['onImportStart']])) {
                    call_user_func([$this, $importItem['onImportStart']]);
                } else if (is_callable([$this, $importItem['onImportStart']])) {
                    call_user_func($importItem['onImportStart']);
                }
            }

            // CALLBACK: onImport
            if ($importItem['data']) {
                $path = $importItem['data']->store('temp');
                Excel::import(
                    new ImportExcel(
                        $this,
                        $importItem['onImport'],
                        isset($importItem['skip_rows']) ? $importItem['skip_rows'] : null
                    ),
                    Storage::path($path)
                );
                Storage::delete($path);
            }

            // CALLBACK: onImportDone
            if (isset($importItem['onImportDone'])) {
                if (is_callable([$this, $importItem['onImportDone']])) {
                    call_user_func([$this, $importItem['onImportDone']]);
                } else if (is_callable([$this, $importItem['onImportDone']])) {
                    call_user_func($importItem['onImportDone']);
                }
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
