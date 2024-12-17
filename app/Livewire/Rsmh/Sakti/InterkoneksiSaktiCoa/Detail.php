<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiCoa;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Kode Harus Diisi', onUpdate: false)]
    public $kode;

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $interkoneksi_sakti_kbki = InterkoneksiSaktiCoaRepository::find($id);

            $this->kode = $interkoneksi_sakti_kbki->kode;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('interkoneksi_sakti_coa.edit', $this->objId);
        } else {
            $this->redirectRoute('interkoneksi_sakti_coa.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('interkoneksi_sakti_coa.index');
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'kode' => $this->kode,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                InterkoneksiSaktiCoaRepository::update($objId, $validatedData);
            } else {
                $obj = InterkoneksiSaktiCoaRepository::create($validatedData);
                $objId = $obj->id;
            }

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
        return view('livewire.rsmh.sakti.interkoneksi-sakti-coa.detail');
    }
}
