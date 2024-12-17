<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiKbki;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Harus Diisi', onUpdate: false)]
    public $nama;

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $interkoneksi_sakti_kbki = InterkoneksiSaktiKbkiRepository::find($id);

            $this->nama = $interkoneksi_sakti_kbki->nama;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('interkoneksi_sakti_kbki.edit', $this->objId);
        } else {
            $this->redirectRoute('interkoneksi_sakti_kbki.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('interkoneksi_sakti_kbki.index');
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'nama' => $this->nama,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                InterkoneksiSaktiKbkiRepository::update($objId, $validatedData);
            } else {
                $obj = InterkoneksiSaktiKbkiRepository::create($validatedData);
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
        return view('livewire.rsmh.sakti.interkoneksi-sakti-kbki.detail');
    }
}
