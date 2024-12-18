<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiDetailCoa;

use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Repositories\Rsmh\Sakti\GenerateInterkoneksiSaktiDetailCoa\GenerateInterkoneksiSaktiDetailCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;

class Detail extends Component
{
    public $isGenerateProcess;
    
    public function mount()
    {
        $this->refreshProgress();
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        $this->redirectRoute('interkoneksi_sakti_detail_coa.index');
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('interkoneksi_sakti_detail_coa.index');
    }

    public function refreshProgress()
    {
        $this->isGenerateProcess = GenerateInterkoneksiSaktiDetailCoaRepository::findBy(whereClause:[
            ['is_done', false]
        ]);
    }

    public function store()
    {
        if($this->isGenerateProcess)
        {
            Alert::fail($this, "Gagal", "Sudah Terdapat Proses");
            return;
        }

        try {
            DB::beginTransaction();
            $data = GenerateInterkoneksiSaktiDetailCoaRepository::getData();
            
            $validatedData = [
                'total' => $data->count()
            ];
            $obj = GenerateInterkoneksiSaktiDetailCoaRepository::create($validatedData);
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.rsmh.sakti.interkoneksi-sakti-detail-coa.detail');
    }
}
